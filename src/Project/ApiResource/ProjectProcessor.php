<?php

declare(strict_types=1);

namespace App\Project\ApiResource;

use ApiPlatform\Metadata\DeleteOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Project\Project;
use App\Project\ProjectRepository;
use App\Project\ProjectUpdatedEvent;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Exception;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final readonly class ProjectProcessor implements ProcessorInterface
{
    public function __construct(
        private ProjectRepository $projectRepository,
        private EntityManagerInterface $entityManager,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    /**
     * @param ProjectResource      $data
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     *
     * @throws EntityNotFoundException
     * @throws Exception
     */
    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = [],
    ): ProjectResource {
        $project = new Project();
        $isNew = true;
        if (isset($uriVariables['id'])) {
            $project = $this->projectRepository->getOneByUuid($uriVariables['id']);
            $isNew = false;
        }

        if ($operation instanceof DeleteOperationInterface) {
            $this->entityManager->remove($project);
            $this->entityManager->flush();

            return $data;
        }

        $project->setTitle($data->title);
        $project->setDescription($data->description);
        $project->setDueDate(new DateTimeImmutable($data->dueDate));
        $project->setClient($data->client);
        $project->setCompany($data->company);

        if (!$isNew) {
            $this->eventDispatcher->dispatch(new ProjectUpdatedEvent($project));
        }

        $this->entityManager->persist($project);
        $this->entityManager->flush();

        return ProjectResource::fromProjectEntity($project);
    }
}
