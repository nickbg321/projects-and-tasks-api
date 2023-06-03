<?php

declare(strict_types=1);

namespace App\Project\Command;

use App\Project\ProjectRepository;
use App\Project\ProjectStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:fail-overdue-projects', description: 'Marks overdue projects as failed')]
class FailOverdueProjectsCommand extends Command
{
    public function __construct(
        private readonly ProjectRepository $projectRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Fetching overdue projects...');

        $overdueProject = $this->projectRepository->findOverdueProjects();
        foreach ($overdueProject as $project) {
            $project->setStatus(ProjectStatus::Failed);
            $output->writeln(sprintf('Marked project %s as failed.', $project->getId()));
        }

        $this->entityManager->flush();
        $output->writeln('Done.');

        return Command::SUCCESS;
    }
}
