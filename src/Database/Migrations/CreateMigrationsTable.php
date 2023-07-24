<?php

declare(strict_types=1);

namespace Tempest\Database\Migrations;

use Tempest\Database\Builder\IdRow;
use Tempest\Database\Builder\TableBuilder;
use Tempest\Database\Builder\TextRow;

final readonly class CreateMigrationsTable implements \Tempest\Interface\Migration
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
