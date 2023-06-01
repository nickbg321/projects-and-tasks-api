<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Enum\ProjectStatus;
use App\Repository\ProjectRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
#[ORM\Table(name: 'projects')]
#[Gedmo\SoftDeleteable]
class Project
{
    use SoftDeleteableEntity;

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private Uuid $id;

    #[ORM\Column]
    private string $title;

    #[ORM\Column(type: Types::TEXT)]
    private string $description;

    #[ORM\Column]
    private ProjectStatus $status = ProjectStatus::New;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private DateTimeInterface $dueDate;

    #[ORM\Column(nullable: true)]
    private ?string $client = null;

    #[ORM\Column(nullable: true)]
    private ?string $company = null;

    /**
     * @var Collection<int, Task>
     */
    #[ORM\OneToMany(mappedBy: 'project', targetEntity: Task::class, orphanRemoval: true)]
    private Collection $tasks;

    public function __construct(string $title, string $description, DateTimeInterface $dueDate)
    {
        $this->id = Uuid::v7();
        $this->title = $title;
        $this->description = $description;
        $this->dueDate = $dueDate;
        $this->tasks = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getStatus(): ProjectStatus
    {
        return $this->status;
    }

    public function setStatus(ProjectStatus $status): void
    {
        $this->status = $status;
    }

    public function getDueDate(): ?DateTimeInterface
    {
        return $this->dueDate;
    }

    public function setDueDate(DateTimeInterface $dueDate): void
    {
        $this->dueDate = $dueDate;
    }

    public function getClient(): ?string
    {
        return $this->client;
    }

    public function setClient(?string $client): void
    {
        $this->client = $client;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(?string $company): void
    {
        $this->company = $company;
    }

    /**
     * @return Collection<int, Task>
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(Task $task): void
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks->add($task);
            $task->setProject($this);
        }
    }

    public function removeTask(Task $task): void
    {
        $this->tasks->removeElement($task);
    }
}
