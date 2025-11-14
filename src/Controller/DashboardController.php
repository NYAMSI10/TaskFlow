<?php

namespace App\Controller;

use App\Entity\Shared;
use App\Entity\Task;
use App\Form\SharedType;
use App\Form\TaskType;
use App\Repository\SharedRepository;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use function Flasher\Prime\flash;

final class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(TaskRepository $taskRepository): Response
    {
        $user = $this->getUser();

        // Récupérer toutes les tâches de l'utilisateur
        $allTasks = $taskRepository->findBy(['user' => $user]);

        // Nombre de tâches par statut
        $tasksByStatus = [];
        foreach ($allTasks as $task) {
            $status = $task->getStatus();
            if (!isset($tasksByStatus[$status])) {
                $tasksByStatus[$status] = 0;
            }
            $tasksByStatus[$status]++;
        }

        // Tâches urgentes (priorité haute) et en retard
        $today = new \DateTime();
        $urgentTasks = [];
        $lateTasks = [];

        foreach ($allTasks as $task) {
            if ($task->getPriority() === 'high' && $task->getStatus() !== 'done') {
                $urgentTasks[] = $task;
            }

            if ($task->getDeadline() && $task->getDeadline() < $today && $task->getStatus() !== 'done') {
                $lateTasks[] = $task;
            }
        }

        // Fusionner et dédupliquer les tâches urgentes et en retard
        $criticalTasks = array_merge($urgentTasks, $lateTasks);
        $criticalTasksUnique = [];
        $taskIds = [];

        foreach ($criticalTasks as $task) {
            if (!in_array($task->getId(), $taskIds)) {
                $taskIds[] = $task->getId();
                $criticalTasksUnique[] = $task;
            }
        }

        // Trier par deadline
        usort($criticalTasksUnique, function ($a, $b) {
            $deadlineA = $a->getDeadline() ?? new \DateTime('2099-12-31');
            $deadlineB = $b->getDeadline() ?? new \DateTime('2099-12-31');
            return $deadlineA <=> $deadlineB;
        });

        return $this->render('dashboard/index.html.twig', [
            'tasksByStatus' => $tasksByStatus,
            'criticalTasks' => $criticalTasksUnique,
            'totalTasks' => count($allTasks),
        ]);
    }
    #[Route('/task', name: 'app_task')]
    public function task(Request $request, TaskRepository $taskRepository): Response
    {
        // Récupération des paramètres de filtre et tri
        $view = $request->query->get('view', 'all_tasks');
        $status = $request->query->get('status', '');
        $priority = $request->query->get('priority', '');
        $sort = $request->query->get('sort', 'date_asc');

        // Utilisation de la méthode du repository
        $tasks = $taskRepository->findByFilters(
            $this->getUser(),
            $view,
            $status ?: null,
            $priority ?: null,
            $sort
        );

        return $this->render('dashboard/task/list.html.twig', [
            'tasks' => $tasks,
            'current_view' => $view,
            'current_status' => $status,
            'current_priority' => $priority,
            'current_sort' => $sort,
        ]);
    }
    #[Route('/add-share-talk', name: 'app_add_share_talk')]
    public function addShareTalk(Request $request, EntityManagerInterface $em): Response
    {
        $shared = new Shared();
        $form = $this->createForm(SharedType::class, $shared);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($shared);
            $em->flush();

            flash()->success('Tâche partagée avec succès !');
            return $this->redirectToRoute('app_task');
        }
        return $this->render('dashboard/shared/_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/edit-share-task/{id}', name: 'app_edit_share_task')]
    public function editShareTask(int $id, Request $request, EntityManagerInterface $em, SharedRepository $sharedRepository): Response
    {
        // find() pour récupérer un objet unique par son ID
        $shared = $sharedRepository->findOneBy(['task' => $id]);
        if (!$shared) {
            flash()->error('Partage introuvable');
            return $this->redirectToRoute('app_task');
        }

        $form = $this->createForm(SharedType::class, $shared);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush(); // Pas besoin de persist pour une modification

            flash()->success('Partage modifié avec succès !');
            return $this->redirectToRoute('app_task');
        }

        return $this->render('dashboard/shared/_form.html.twig', [
            'form' => $form->createView(),
            'form_title' => 'Modifier le partage',
            'button_label' => 'Modifier'
        ]);
    }
    #[Route('/add-task', name: 'app_add_task')]
    public function addTask(Request $request, EntityManagerInterface $em): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $task->setUser($this->getUser());
            $em->persist($task);
            $em->flush();

            flash()->success('Tâche créée avec succès !');
            return $this->redirectToRoute('app_task');
        }
        return $this->render('dashboard/task/form.html.twig', [
            'form' => $form->createView(),
            'is_edit' => false

        ]);
    }
    #[Route('/edit-task/{id}', name: 'app_edit_task')]
    public function editTask(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $task = $em->getRepository(Task::class)->find($id);
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($task);
            $em->flush();

            flash()->success('Tâche modifiée avec succès !');
            return $this->redirectToRoute('app_task');
        }
        return $this->render('dashboard/task/form.html.twig', [
            'form' => $form->createView(),
            'is_edit' => true
        ]);
    }
    #[Route('/delete-task/{id}', name: 'app_delete_task')]
    public function deleteTask(Task $task, EntityManagerInterface $em)
    {
        $em->remove($task);
        $em->flush();
        flash()->success('Tâche supprimée avec succès !');

        return $this->redirectToRoute('app_task');
    }
}
