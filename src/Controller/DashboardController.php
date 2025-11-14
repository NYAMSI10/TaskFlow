<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
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
    public function index(): Response
    {
        return $this->render('dashboard/index.html.twig');
    }
    #[Route('/task', name: 'app_task')]
    public function task(TaskRepository $taskRepository): Response
    {
        $tasks = $taskRepository->findAll();
        return $this->render('dashboard/task/list.html.twig', ['tasks' => $tasks]);
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
