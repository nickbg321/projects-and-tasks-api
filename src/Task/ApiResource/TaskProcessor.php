<?php

declare(strict_types=1);

namespace App\Task\ApiResource;

use ApiPlatform\Metadata\DeleteOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Project\ProjectRepository;
use App\Project\ProjectUpdatedEvent;
use App\Task\Task;
use App\Task\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final readonly class TaskProcessor implements ProcessorInterface
{
    public function __construct(
        private TaskRepository $taskRepository,
        private ProjectRepository $projectRepository,
        private EntityManagerInterface $entityManager,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    /**
     * @param TaskResource         $data
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     *
     * @throws EntityNotFoundException
     */
    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = [],
    ): TaskResource {
        $project = $this->projectRepository->getOneByUuid($data->project->id);

        $task = new Task();
        if (isset($uriVariables['id'])) {
            $task = $this->taskRepository->getOneByUuid($uriVariables['id']);
        }

        if ($operation instanceof DeleteOperationInterface) {
            $project->removeTask($task);

            $this->eventDispatcher->dispatch(new ProjectUpdatedEvent($project));
            $this->entityManager->remove($task);
            $this->entityManager->flush();

            return $data;
        }

        $task->setDescription($data->description);
        $task->setProject($project);
        $task->setIsCompleted($data->isCompleted);
        $project->addTask($task);

        $this->eventDispatcher->dispatch(new ProjectUpdatedEvent($project));
        $this->entityManager->persist($task);
        $this->entityManager->flush();

        return TaskResource::fromTaskEntity($task);
    }
}
