<?php

declare(strict_types=1);

namespace App\Task\ApiResource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model\Operation;
use App\Project\ApiResource\ProjectResource;
use App\Task\Task;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'Task',
    operations: [
        new Get(),
        new GetCollection(),
        new Put(),
        new Post(),
        new Patch(),
        new Delete(),
        new GetCollection(
            uriTemplate: '/projects/{id}/tasks',
            uriVariables: [
                'id' => new Link(fromProperty: 'id', fromClass: ProjectResource::class),
            ],
            openapi: new Operation(summary: self::OPENAPI_DESCRIPTION, description: self::OPENAPI_DESCRIPTION,),
            paginationEnabled: false,
            provider: ProjectTaskProvider::class,
        ),
    ],
    normalizationContext: ['groups' => [self::READ_GROUP]],
    denormalizationContext: ['groups' => [self::WRITE_GROUP]],
    provider: TaskProvider::class,
    processor: TaskProcessor::class,
)]
class TaskResource
{
    final public const READ_GROUP = 'task:read';
    final public const WRITE_GROUP = 'task:write';

    private const OPENAPI_DESCRIPTION = 'Retrieves the collection of Task resources assigned to a Project resource.';

    #[ApiProperty(required: true, identifier: true)]
    #[Groups(([self::READ_GROUP]))]
    public Uuid $id;

    #[Assert\NotBlank(normalizer: 'trim')]
    #[Groups([self::READ_GROUP, self::WRITE_GROUP])]
    public string $description;

    #[Assert\NotNull]
    #[Groups([self::READ_GROUP, self::WRITE_GROUP])]
    public bool $isCompleted;

    #[Assert\NotNull]
    #[Groups([self::READ_GROUP, self::WRITE_GROUP])]
    public ProjectResource $project;

    public static function fromTaskEntity(Task $task): self
    {
        $resource = new self();
        $resource->id = $task->getId();
        $resource->description = $task->getDescription();
        $resource->isCompleted = $task->isCompleted();
        $resource->project = ProjectResource::fromProjectEntity($task->getProject());

        return $resource;
    }
}
