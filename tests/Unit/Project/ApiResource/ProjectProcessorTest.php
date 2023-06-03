<?php

namespace App\Tests\Unit\Project\ApiResource;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Project\ApiResource\ProjectProcessor;
use App\Project\ApiResource\ProjectResource;
use App\Project\Project;
use App\Project\ProjectRepository;
use App\Project\ProjectStatus;
use App\Project\ProjectUpdatedEvent;
use App\Tests\UnitTestCase;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class ProjectProcessorTest extends UnitTestCase
{
    private ObjectProphecy|ProjectRepository $projectRepository;
    private ObjectProphecy|EntityManagerInterface $entityManager;
    private ObjectProphecy|EventDispatcherInterface $eventDispatcher;
    private ProjectProcessor $processor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->projectRepository = $this->prophesize(ProjectRepository::class);
        $this->entityManager = $this->prophesize(EntityManagerInterface::class);
        $this->eventDispatcher = $this->prophesize(EventDispatcherInterface::class);

        $this->processor = new ProjectProcessor(
            projectRepository: $this->projectRepository->reveal(),
            entityManager: $this->entityManager->reveal(),
            eventDispatcher: $this->eventDispatcher->reveal(),
        );
    }

    public function testProjectIsCreated(): void
    {
        $data = $this->getProjectResource();

        $this->eventDispatcher->dispatch(Argument::any())
            ->shouldNotBeCalled();
        $this->entityManager->persist(Argument::type(Project::class))
            ->shouldBeCalledOnce();
        $this->entityManager->flush()
            ->shouldBeCalledOnce();

        $result = $this->processor->process($data, new Post());

        $this->executeAsserts($data, $result);
    }

    public function testProjectIsUpdated(): void
    {
        $data = $this->getProjectResource();
        $project = new Project();

        $this->projectRepository->getOneByUuid($project->getId())
            ->shouldBeCalledOnce()
            ->willReturn($project);
        $this->eventDispatcher->dispatch(Argument::that(function (ProjectUpdatedEvent $event) use ($project) {
            $this->assertSame($project, $event->project);

            return true;
        }))
            ->shouldBeCalledOnce();
        $this->entityManager->persist($project)
            ->shouldBeCalledOnce();
        $this->entityManager->flush()
            ->shouldBeCalledOnce();

        $result = $this->processor->process($data, new Put(), ['id' => $project->getId()]);

        $this->executeAsserts($data, $result);
    }

    public function testProjectIsDeleted(): void
    {
        $data = $this->getProjectResource();
        $project = new Project();

        $this->projectRepository->getOneByUuid($project->getId())
            ->shouldBeCalledOnce()
            ->willReturn($project);
        $this->entityManager->remove($project)
            ->shouldBeCalledOnce();
        $this->entityManager->flush()
            ->shouldBeCalledOnce();

        $result = $this->processor->process($data, new Delete(), ['id' => $project->getId()]);

        $this->assertSame($data, $result);
    }

    private function getProjectResource(): ProjectResource
    {
        $data = new ProjectResource();
        $data->id = Uuid::v7();
        $data->title = 'My project';
        $data->description = 'Project description';
        $data->dueDate = new DateTimeImmutable('2023-05-28');
        $data->status = ProjectStatus::New->value;
        $data->client = 'John Doe';
        $data->company = 'Acme corp.';

        return $data;
    }

    private function executeAsserts(ProjectResource $expected, ProjectResource $actual): void
    {
        $this->assertInstanceOf(Uuid::class, $actual->id);
        $this->assertEquals($expected->title, $actual->title);
        $this->assertEquals($expected->description, $actual->description);
        $this->assertEquals($expected->dueDate, $actual->dueDate);
        $this->assertEquals($expected->status, $actual->status);
        $this->assertEquals($expected->client, $actual->client);
        $this->assertEquals($expected->company, $actual->company);
    }
}
