<?php

namespace Tempest\Database\Compilers;

use Tempest\Database\TableDefinitions\TableDefinition;

interface TableCompiler
{
    public function compile(TableDefinition $tableDefinition): string;
}