<?php

declare(strict_types=1);

namespace Tempest\Database\Migrations;

use ArrayIterator;
use IteratorAggregate;
use Tempest\Database\DatabaseMigration;
use Traversable;

/** @implements IteratorAggregate<DatabaseMigration> */
final class RunnableMigrations implements IteratorAggregate
{
    /**
     * @param DatabaseMigration[] $migrations
     */
    public function __construct(
        private array $migrations = [],
    ) {
        usort($this->migrations, static fn (DatabaseMigration $a, DatabaseMigration $b) => $a->name <=> $b->name);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->migrations);
    }
}
