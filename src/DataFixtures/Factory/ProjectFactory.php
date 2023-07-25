<?php

declare(strict_types=1);

namespace App\DataFixtures\Factory;

use App\Project\Project;
use App\Project\ProjectRepository;
use App\Project\ProjectStatus;
use DateTimeImmutable;
use Random\Randomizer;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Project>
 *
 * @method        Project|Proxy                     create(array|callable $attributes = [])
 * @method static Project|Proxy                     createOne(array $attributes = [])
 * @method static Project|Proxy                     find(object|array|mixed $criteria)
 * @method static Project|Proxy                     findOrCreate(array $attributes)
 * @method static Project|Proxy                     first(string $sortedField = 'id')
 * @method static Project|Proxy                     last(string $sortedField = 'id')
 * @method static Project|Proxy                     random(array $attributes = [])
 * @method static Project|Proxy                     randomOrCreate(array $attributes = [])
 * @method static ProjectRepository|RepositoryProxy repository()
 * @method static Project[]|Proxy[]                 all()
 * @method static Project[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static Project[]|Proxy[]                 createSequence(iterable|callable $sequence)
 * @method static Project[]|Proxy[]                 findBy(array $attributes)
 * @method static Project[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static Project[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 */
final class ProjectFactory extends ModelFactory
{
    public function pending(): self
    {
        return $this->addState([
            'status' => ProjectStatus::Pending,
        ]);
    }

    public function completed(): self
    {
        return $this->addState([
            'status' => ProjectStatus::Done,
        ]);
    }

    public function overdue(): self
    {
        return $this->addState([
            'status' => ProjectStatus::Failed,
            'dueDate' => DateTimeImmutable::createFromMutable(self::faker()->dateTimeBetween('-6 months', 'yesterday')),
        ]);
    }

    protected function getDefaults(): array
    {
        $randomizer = new Randomizer();

        $client = null;
        $company = null;
        $types = ['client', 'company', 'both'];
        $typeKey = current($randomizer->pickArrayKeys($types, 1));

        switch ($types[$typeKey]) {
            case 'client':
                $client = self::faker()->firstName() . ' ' . self::faker()->lastName();
                break;
            case 'company':
                $company = self::faker()->company();
                break;
            case 'both':
                $client = self::faker()->firstName() . ' ' . self::faker()->lastName();
                $company = self::faker()->company();
                break;
        }

        /** @var string $title */
        $title = self::faker()->words($randomizer->getInt(3, 7), true);

        return [
            'description' => self::faker()->text($randomizer->getInt(100, 300)),
            'dueDate' => DateTimeImmutable::createFromMutable(self::faker()->dateTimeBetween('tomorrow', '+1 year')),
            'status' => ProjectStatus::New,
            'title' => ucfirst($title),
            'client' => $client,
            'company' => $company,
        ];
    }

    protected static function getClass(): string
    {
        return Project::class;
    }
}
