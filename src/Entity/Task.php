<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
#[ORM\Table(name: 'tasks')]
#[Gedmo\SoftDeleteable]
class Task
{
    use SoftDeleteableEntity;

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private Uuid $id;

    #[ORM\Column(type: Types::TEXT)]
    private string $description;

    #[ORM\Column]
    private bool $isCompleted = false;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    #[ORM\JoinColumn(nullable: false)]
    private Project $project;

    public function __construct(string $description, Project $project)
    {
        $this->id = Uuid::v7();
        $this->description = $description;
        $this->project = $project;

        $project->addTask($this);
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function isCompleted(): bool
    {
        return $this->isCompleted;
    }

    public function markAsCompleted(): void
    {
        $this->isCompleted = true;
    }

    public function markAsPending(): void
    {
        $this->isCompleted = false;
    }

    public function getProject(): Project
    {
        return $this->project;
    }

    public function setProject(Project $project): void
    {
        $this->project = $project;
    }
}
