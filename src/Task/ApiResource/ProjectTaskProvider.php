<?php

declare(strict_types=1);

namespace App\Task\ApiResource;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\Project\ProjectRepository;
use App\Task\Task;
use App\Task\TaskRepository;
use ArrayIterator;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

/**
 * @implements ProviderInterface<TaskResource>
 */
final readonly class ProjectTaskProvider implements ProviderInterface
{
    public function __construct(
        private TaskRepository $taskRepository,
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
     * @throws EntityNotFoundException
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): TraversablePaginator
    {
        $project = $this->projectRepository->getOneByUuid($uriVariables['id']);

        [$page, $offset, $limit] = $this->pagination->getPagination($operation, $context);

        $iterator = new ArrayIterator(
            array_map(
                fn (Task $task) => TaskResource::fromTaskEntity($task),
                $this->taskRepository->findTasksForProject($project, $limit, $offset),
            ),
        );

        return new TraversablePaginator($iterator, $page, $limit, $this->taskRepository->getCountForProject($project));
    }
}
