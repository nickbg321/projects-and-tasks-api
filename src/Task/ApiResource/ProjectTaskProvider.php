<?php

declare(strict_types=1);

namespace App\Task\ApiResource;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Project\ProjectRepository;
use App\Task\Task;
use Doctrine\ORM\EntityNotFoundException;

/**
 * @implements ProviderInterface<TaskResource>
 */
final readonly class ProjectTaskProvider implements ProviderInterface
{
    public function __construct(
        private ProjectRepository $projectRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     *
     * @throws EntityNotFoundException
     *
     * @return array<TaskResource>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $project = $this->projectRepository->getOneByUuid($uriVariables['id']);

        return array_map(fn (Task $task) => TaskResource::fromTaskEntity($task), $project->getTasks()->toArray());
    }
}
