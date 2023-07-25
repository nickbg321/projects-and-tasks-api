<?php

declare(strict_types=1);

namespace App\DataFixtures\Story;

use App\DataFixtures\Factory\ProjectFactory;
use App\DataFixtures\Factory\TaskFactory;
use Random\Randomizer;
use Zenstruck\Foundry\Story;

final class ProjectStory extends Story
{
    public function build(): void
    {
        $randomizer = new Randomizer();

        // create projects
        $completedProjects = ProjectFactory::new()
            ->completed()
            ->many(5)
            ->create();
        $overdueProjects = ProjectFactory::new()
            ->overdue()
            ->many(5)
            ->create();
        $pendingProjects = ProjectFactory::new()
            ->pending()
            ->many(10)
            ->create();
        $newProjects = ProjectFactory::createMany(30);

        // add tasks to projects
        foreach ($completedProjects as $project) {
            TaskFactory::new()
                ->completed()
                ->many($randomizer->getInt(1, 10))
                ->create([
                    'project' => $project,
                ]);
        }

        foreach ($overdueProjects as $project) {
            TaskFactory::createMany($randomizer->getInt(1, 10), [
                'project' => $project,
            ]);
        }

        foreach ($pendingProjects as $project) {
            TaskFactory::new()
                ->completed()
                ->many($randomizer->getInt(1, 5))
                ->create([
                    'project' => $project,
                ]);

            TaskFactory::new()
                ->pending()
                ->many($randomizer->getInt(1, 5))
                ->create([
                    'project' => $project,
                ]);
        }

        foreach ($newProjects as $project) {
            TaskFactory::new()
                ->pending()
                ->many($randomizer->getInt(0, 10))
                ->create([
                    'project' => $project,
                ]);
        }
    }
}
