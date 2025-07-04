<?php

namespace Tests\Tempest\Integration\ORM\Migrations;

use Tempest\Database\DatabaseMigration;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tests\Tempest\Integration\ORM\Models\DateTimeModel;

final class CreateDateTimeModelTable implements DatabaseMigration
{
    public string $name = '0001_datetime_model_table';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(DateTimeModel::class)
            ->primary()
            ->datetime('phpDateTime')
            ->datetime('tempestDateTime');
    }

    public function down(): null
    {
        return null;
    }
}
