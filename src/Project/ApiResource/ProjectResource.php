<?php

declare(strict_types=1);

namespace App\Project\ApiResource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Project\Project;
use App\Project\Validator as CustomAssert;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'Project',
    operations: [new Get(), new GetCollection(), new Put(), new Post(), new Patch(), new Delete()],
    normalizationContext: ['groups' => [self::READ_GROUP]],
    denormalizationContext: ['groups' => [self::WRITE_GROUP]],
    provider: ProjectProvider::class,
    processor: ProjectProcessor::class,
)]
#[CustomAssert\ClientOrCompany]
class ProjectResource
{
    final public const READ_GROUP = 'project:read';
    final public const WRITE_GROUP = 'project:write';

    #[ApiProperty(required: true, identifier: true)]
    #[Groups(([self::READ_GROUP]))]
    public Uuid $id;

    #[Assert\NotBlank(normalizer: 'trim')]
    #[Assert\Length(max: 255)]
    #[Groups([self::READ_GROUP, self::WRITE_GROUP])]
    public string $title;

    #[Assert\NotBlank(normalizer: 'trim')]
    #[Assert\Length(max: 1000)]
    #[Groups([self::READ_GROUP, self::WRITE_GROUP])]
    public string $description;

    #[Groups([self::READ_GROUP])]
    public string $status;

    #[Assert\NotBlank(normalizer: 'trim')]
    #[Assert\Date]
    #[Groups([self::READ_GROUP, self::WRITE_GROUP])]
    public string $dueDate;

    #[Assert\Length(max: 255)]
    #[Groups([self::READ_GROUP, self::WRITE_GROUP])]
    public ?string $client = null;

    #[Assert\Length(max: 255)]
    #[Groups([self::READ_GROUP, self::WRITE_GROUP])]
    public ?string $company = null;

    public static function fromProjectEntity(Project $project): self
    {
        $resource = new self();
        $resource->id = $project->getId();
        $resource->title = $project->getTitle();
        $resource->description = $project->getDescription();
        $resource->status = $project->getStatus()->value;
        $resource->dueDate = $project->getDueDate()->format('Y-m-d');
        $resource->client = $project->getClient();
        $resource->company = $project->getCompany();

        return $resource;
    }
}
