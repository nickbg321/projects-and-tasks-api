<?php

declare(strict_types=1);

namespace App\Project;

use DateTimeImmutable;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class ProjectStatusSubscriber implements EventSubscriberInterface
{
    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ProjectUpdatedEvent::class => 'onProjectUpdated',
        ];
    }

    public function onProjectUpdated(ProjectUpdatedEvent $event): void
    {
        $project = $event->project;
        $project->setStatus($this->getProjectStatus($project));
    }

    private function getProjectStatus(Project $project): ProjectStatus
    {
        $now = new DateTimeImmutable();
        if ($project->getDueDate() < $now) {
            return ProjectStatus::Failed;
        }

        $allTasks = $project->getTasks();
        $pendingTasks = $project->getPendingTasks();

        if ($allTasks->isEmpty() || $pendingTasks->count() === $allTasks->count()) {
            return ProjectStatus::New;
        }

        if (!$pendingTasks->isEmpty()) {
            return ProjectStatus::Pending;
        }

        return ProjectStatus::Done;
    }
}
