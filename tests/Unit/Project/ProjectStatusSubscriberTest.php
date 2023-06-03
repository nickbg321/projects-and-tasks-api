<?php

namespace App\Tests\Unit\Project;

use App\Project\Project;
use App\Project\ProjectStatus;
use App\Project\ProjectStatusSubscriber;
use App\Project\ProjectUpdatedEvent;
use App\Task\Task;
use App\Tests\UnitTestCase;
use DateTimeImmutable;

final class ProjectStatusSubscriberTest extends UnitTestCase
{
    private ProjectStatusSubscriber $subscriber;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subscriber = new ProjectStatusSubscriber();
    }

    public function testGetSubscribedEvents(): void
    {
        $this->assertEquals(
            [
                ProjectUpdatedEvent::class => 'onProjectUpdated',
            ],
            ProjectStatusSubscriber::getSubscribedEvents(),
        );
    }

    /**
     * @dataProvider dataProvider
     */
    public function testOnProjectUpdated(Project $project, ProjectStatus $expectedStatus): void
    {
        $this->subscriber->onProjectUpdated(new ProjectUpdatedEvent($project));

        $this->assertEquals($expectedStatus, $project->getStatus());
    }

    public static function dataProvider(): array
    {
        $pendingTask1 = new Task();
        $pendingTask2 = new Task();
        $completedTask = new Task();
        $completedTask->setIsCompleted(true);

        $overdueProject = new Project();
        $overdueProject->setDueDate(new DateTimeImmutable('2020-02-10'));
        $projectWithoutTasks = new Project();
        $projectWithoutTasks->setDueDate(new DateTimeImmutable('2030-02-10'));
        $projectWithPendingTasksOnly = new Project();
        $projectWithPendingTasksOnly->setDueDate(new DateTimeImmutable('2030-02-10'));
        $projectWithPendingTasksOnly->addTask($pendingTask1);
        $projectWithMixedTasks = new Project();
        $projectWithMixedTasks->setDueDate(new DateTimeImmutable('2030-02-10'));
        $projectWithMixedTasks->addTask($pendingTask1);
        $projectWithMixedTasks->addTask($pendingTask2);
        $projectWithMixedTasks->addTask($completedTask);
        $projectWithCompletedTasks = new Project();
        $projectWithCompletedTasks->setDueDate(new DateTimeImmutable('2030-02-10'));
        $projectWithCompletedTasks->addTask($completedTask);
        $overdueProjectWithCompletedTasks = new Project();
        $overdueProjectWithCompletedTasks->setDueDate(new DateTimeImmutable('2020-02-10'));
        $overdueProjectWithCompletedTasks->addTask($completedTask);

        return [
            'Overdue project is marked as failed' => [
                $overdueProject,
                ProjectStatus::Failed,
            ],
            'Project without tasks is marked as new' => [
                $projectWithoutTasks,
                ProjectStatus::New,
            ],
            'Project with pending tasks only is marked as new' => [
                $projectWithPendingTasksOnly,
                ProjectStatus::New,
            ],
            'Project with mixed tasks is marked as pending' => [
                $projectWithMixedTasks,
                ProjectStatus::Pending,
            ],
            'Project with completed tasks is marked as done' => [
                $projectWithCompletedTasks,
                ProjectStatus::Done,
            ],
            'Overdue project with completed tasks is marked as failed' => [
                $overdueProjectWithCompletedTasks,
                ProjectStatus::Failed,
            ],
        ];
    }
}
