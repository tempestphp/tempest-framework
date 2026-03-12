<?php

declare(strict_types=1);

namespace Tempest\Database\Migrations;

use ArrayIterator;
use IteratorAggregate;
use Tempest\Database\MigratesDown;
use Tempest\Database\MigratesUp;
use Traversable;

/** @implements IteratorAggregate<MigratesUp|MigratesDown> */
final class RunnableMigrations implements IteratorAggregate
{
    /**
     * @param array<MigratesUp|MigratesDown> $migrations
     */
    public function __construct(
        private array $migrations = [],
    ) {
        usort($this->migrations, static fn (MigratesUp|MigratesDown $a, MigratesUp|MigratesDown $b) => strnatcmp($a->name, $b->name));
    }

    /**
     * @return Traversable<MigratesUp|MigratesDown>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->migrations);
    }

    /**
     * @return Traversable<MigratesUp>
     */
    public function up(): Traversable
    {
        foreach ($this->getIterator() as $migration) {
            if ($migration instanceof MigratesUp) {
                yield $migration;
            }
        }
    }

    /**
     * @return Traversable<MigratesDown>
     */
    public function down(): Traversable
    {
        foreach ($this->getIterator() as $migration) {
            if ($migration instanceof MigratesDown) {
                yield $migration;
            }
        }
    }
}
