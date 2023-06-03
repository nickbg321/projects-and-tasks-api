<?php

declare(strict_types=1);

namespace App\Project;

readonly class ProjectUpdatedEvent
{
    public function __construct(
        public Project $project,
    ) {
    }
}
