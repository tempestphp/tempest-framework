<?php

namespace Tempest\Interfaces;

use Tempest\Database\TableBuilder\TableBuilder;

interface DatabaseMigration
{
    public function getName(): string;

    public function up(TableBuilder $builder): TableBuilder;

    public function down(TableBuilder $builder): TableBuilder;
}