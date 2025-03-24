<?php

namespace Tests\Tempest\Integration\ORM\Migrations;

use Tempest\Database\DatabaseMigration;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;
use Tests\Tempest\Integration\ORM\Models\CasterEnum;
use Tests\Tempest\Integration\ORM\Models\CasterModel;

final class CreateCasterModelTable implements DatabaseMigration
{
    public string $name = '0000_create_caster_model_table';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(CasterModel::class)
            ->primary()
            ->datetime('date')
            ->array('array')
            ->enum('enum', CasterEnum::class);
    }

    public function down(): QueryStatement
    {
        return DropTableStatement::forModel(CasterModel::class);
    }
}
