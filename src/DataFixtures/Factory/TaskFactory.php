<?php

declare(strict_types=1);

namespace App\DataFixtures\Factory;

use App\Task\Task;
use App\Task\TaskRepository;
use Random\Randomizer;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Task>
 *
 * @method        Task|Proxy                     create(array|callable $attributes = [])
 * @method static Task|Proxy                     createOne(array $attributes = [])
 * @method static Task|Proxy                     find(object|array|mixed $criteria)
 * @method static Task|Proxy                     findOrCreate(array $attributes)
 * @method static Task|Proxy                     first(string $sortedField = 'id')
 * @method static Task|Proxy                     last(string $sortedField = 'id')
 * @method static Task|Proxy                     random(array $attributes = [])
 * @method static Task|Proxy                     randomOrCreate(array $attributes = [])
 * @method static TaskRepository|RepositoryProxy repository()
 * @method static Task[]|Proxy[]                 all()
 * @method static Task[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static Task[]|Proxy[]                 createSequence(iterable|callable $sequence)
 * @method static Task[]|Proxy[]                 findBy(array $attributes)
 * @method static Task[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static Task[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 */
final class TaskFactory extends ModelFactory
{
    public function completed(): self
    {
        return $this->addState([
            'isCompleted' => true,
        ]);
    }

    public function pending(): self
    {
        return $this->addState([
            'isCompleted' => false,
        ]);
    }

    protected function getDefaults(): array
    {
        $randomizer = new Randomizer();

        /** @var string $description */
        $description = self::faker()->words($randomizer->getInt(3, 7), true);

        return [
            'description' => ucfirst($description),
            'isCompleted' => self::faker()->boolean(),
            'project' => ProjectFactory::new(),
        ];
    }

    protected static function getClass(): string
    {
        return Task::class;
    }
}
