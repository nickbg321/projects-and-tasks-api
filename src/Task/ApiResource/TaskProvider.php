<?php

declare(strict_types=1);

namespace App\Task\ApiResource;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\Task\Task;
use App\Task\TaskRepository;
use ArrayIterator;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

/**
 * @implements ProviderInterface<TaskResource>
 */
final readonly class TaskProvider implements ProviderInterface
{
    public function __construct(
        private TaskRepository $taskRepository,
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
    ): TaskResource|TraversablePaginator|null {
        if ($operation instanceof CollectionOperationInterface) {
            [$page, $offset, $limit] = $this->pagination->getPagination($operation, $context);

            $iterator = new ArrayIterator(
                array_map(
                    fn (Task $task) => TaskResource::fromTaskEntity($task),
                    $this->taskRepository->findTasks($limit, $offset),
                ),
            );

            return new TraversablePaginator($iterator, $page, $limit, $this->taskRepository->getTotalCount());
        }

        $task = $this->taskRepository->findOneByUuid($uriVariables['id']);
        if (!$task instanceof Task) {
            return null;
        }

        return TaskResource::fromTaskEntity($task);
    }
}
