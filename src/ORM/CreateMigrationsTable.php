<?php

namespace Tempest\ORM;

use Tempest\Database\TableBuilder\IdRow;
use Tempest\Database\TableBuilder\TableBuilder;
use Tempest\Database\TableBuilder\TextRow;

final readonly class CreateMigrationsTable implements \Tempest\Interfaces\DatabaseMigration
{
    public function getName(): string
    {
        return '0000-00-00_create_migrations_table';
    }

    public function up(TableBuilder $builder): TableBuilder
    {
        return $builder
            ->name(Migration::table())
            ->add(new IdRow())
            ->add(new TextRow('name'))
            ->create()
            ->ifNotExists();
    }

    public function down(TableBuilder $builder): TableBuilder
    {
        return $builder
            ->name(Migration::table())
            ->drop();
    }
}