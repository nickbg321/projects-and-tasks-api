<?php

declare(strict_types=1);

namespace App\Project;

use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<Project>
 *
 * @method Project|null find($id, $lockMode = null, $lockVersion = null)
 * @method Project|null findOneBy(array $criteria, array $orderBy = null)
 * @method Project[]    findAll()
 * @method Project[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    public function findOneByUuid(Uuid $uuid): ?Project
    {
        return $this->find($uuid);
    }

    /**
     * @throws EntityNotFoundException
     */
    public function getOneByUuid(Uuid $uuid): Project
    {
        $project = $this->findOneByUuid($uuid);
        if (!$project instanceof Project) {
            throw EntityNotFoundException::fromClassNameAndIdentifier(Project::class, [(string) $uuid]);
        }

        return $project;
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getTotalCount(): int
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return array<Project>
     */
    public function findProjects(int $limit, int $offset): array
    {
        return $this->createQueryBuilder('p')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->orderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<Project>
     */
    public function findOverdueProjects(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.status IN (:statuses)')
            ->andWhere('p.dueDate < :now')
            ->setParameter('now', (new DateTimeImmutable())->format('Y-m-d'))
            ->setParameter('statuses', [ProjectStatus::New, ProjectStatus::Pending])
            ->getQuery()
            ->getResult();
    }
}
