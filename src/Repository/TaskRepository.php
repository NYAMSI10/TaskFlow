<?php

namespace App\Repository;

use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    /**
     * Trouve les tâches avec filtres et tri
     * 
     * @param mixed $user L'utilisateur propriétaire des tâches
     * @param string $view Type de vue (my_tasks, shared_tasks, all_tasks)
     * @param string|null $status Filtre par statut
     * @param string|null $priority Filtre par priorité
     * @param string $sort Type de tri (date_asc, date_desc, priority)
     * @return Task[] Returns an array of Task objects
     */
    public function findByFilters($user, string $view = 'my_tasks', ?string $status = null, ?string $priority = null, string $sort = 'date_asc'): array
    {
        $qb = $this->createQueryBuilder('t')
            ->leftJoin('t.shared', 's')
            ->leftJoin('s.user', 'su');

        // Filtrage par vue
        switch ($view) {
            case 'shared_tasks':
                // Mes propres tâches que j'ai partagées avec d'autres
                $qb->where('t.user = :user')
                   ->andWhere('s.id IS NOT NULL')
                   ->setParameter('user', $user);
                break;
            case 'all_tasks':
                // Toutes les tâches : mes tâches créées + tâches partagées avec moi
                $qb->where('t.user = :user OR su.id = :userId')
                   ->setParameter('user', $user)
                   ->setParameter('userId', $user->getId());
                break;
            case 'my_tasks':
            default:
                // Tâches qu'un autre utilisateur a partagées avec moi
                $qb->innerJoin('t.shared', 'shared')
                   ->innerJoin('shared.user', 'sharedUser')
                   ->where('sharedUser.id = :userId')
                   ->setParameter('userId', $user->getId());
                break;
        }

        // Filtrage par statut
        if ($status) {
            $qb->andWhere('t.status = :status')
               ->setParameter('status', $status);
        }

        // Filtrage par priorité
        if ($priority) {
            $qb->andWhere('t.priority = :priority')
               ->setParameter('priority', $priority);
        }

        // Tri
        switch ($sort) {
            case 'date_desc':
                $qb->orderBy('t.deadline', 'DESC');
                break;
            case 'priority':
                // Tri par priorité : high > medium > low
                $qb->addOrderBy(
                    'CASE WHEN t.priority = \'high\' THEN 1 WHEN t.priority = \'medium\' THEN 2 ELSE 3 END',
                    'ASC'
                );
                $qb->addOrderBy('t.deadline', 'ASC');
                break;
            case 'date_asc':
            default:
                $qb->orderBy('t.deadline', 'ASC');
                break;
        }

        return $qb->getQuery()->getResult();
    }
}
