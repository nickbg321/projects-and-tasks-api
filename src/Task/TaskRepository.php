<?php

declare(strict_types=1);

namespace App\Task;

use App\Project\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<Task>
 *
 * @method Task|null find($id, $lockMode = null, $lockVersion = null)
 * @method Task|null findOneBy(array $criteria, array $orderBy = null)
 * @method Task[]    findAll()
 * @method Task[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function findOneByUuid(Uuid $uuid): ?Task
    {
        return $this->find($uuid);
    }

    /**
     * @throws EntityNotFoundException
     */
    public function getOneByUuid(Uuid $uuid): Task
    {
        $task = $this->findOneByUuid($uuid);
        if (!$task instanceof Task) {
            throw EntityNotFoundException::fromClassNameAndIdentifier(Task::class, [(string) $uuid]);
        }

        return $task;
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getTotalCount(): int
    {
        return $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return array<Task>
     */
    public function findTasks(int $limit, int $offset): array
    {
        return $this->createQueryBuilder('t')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getCountForProject(Project $project): int
    {
        return $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->andWhere('t.project = :project')
            ->setParameter('project', $project)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return array<Task>
     */
    public function findTasksForProject(Project $project, int $limit, int $offset): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.project = :project')
            ->setParameter('project', $project)
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }
}
