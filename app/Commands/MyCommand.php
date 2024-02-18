<?php

declare(strict_types=1);

namespace App\Commands;

use App\Migrations\CreateAuthorTable;
use App\Migrations\CreateBookTable;
use Tempest\Database\Builder\TableBuilder;
use Tempest\Interface\Migration;

class MyCommand implements Migration
{
    public function getName(): string
    {
        // Provide a unique name for your migration command
        return 'MyMigrationCommand';
    }

    public function up(TableBuilder $builder): TableBuilder
    {
        // Instantiate migrations
        $createAuthorMigration = new CreateAuthorTable();
        $createBookMigration = new CreateBookTable();

        // Run migrations
        $createAuthorMigration->up($builder);
        $createBookMigration->up($builder);

        return $builder;
    }

    public function down(TableBuilder $builder): TableBuilder
    {
        // If you need to implement a down migration,
        // you can do so here. Otherwise, leave this method empty.
        return $builder;
    }
}
