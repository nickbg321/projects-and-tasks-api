<?php

declare(strict_types=1);

namespace App\Project\ApiResource;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\Project\Project;
use App\Project\ProjectRepository;
use ArrayIterator;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

/**
 * @implements ProviderInterface<ProjectResource>
 */
final readonly class ProjectProvider implements ProviderInterface
{
    public function __construct(
        private ProjectRepository $projectRepository,
        private Pagination $pagination,
    ) {
    }

    /**
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function provide(
        Operation $operation,
        array $uriVariables = [],
        array $context = [],
    ): ProjectResource|TraversablePaginator|null {
        if ($operation instanceof CollectionOperationInterface) {
            [$page, $offset, $limit] = $this->pagination->getPagination($operation, $context);

            $iterator = new ArrayIterator(
                array_map(
                    fn (Project $project) => ProjectResource::fromProjectEntity($project),
                    $this->projectRepository->findProjects($limit, $offset),
                ),
            );

            return new TraversablePaginator($iterator, $page, $limit, $this->projectRepository->getTotalCount());
        }

        $project = $this->projectRepository->findOneByUuid($uriVariables['id']);
        if (!$project instanceof Project) {
            return null;
        }

        return ProjectResource::fromProjectEntity($project);
    }
}
