<?php

namespace Tempest\Database\Compilers;

use Tempest\Database\Columns\Column;

interface ColumnCompiler
{
    public function compile(Column $column): string;
}
