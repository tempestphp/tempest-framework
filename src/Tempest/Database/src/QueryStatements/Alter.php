<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatement;

enum Alter: string implements QueryStatement
{
    case ADD = 'ADD';
    case DROP = 'DROP';
    case RENAME = 'RENAME';

    public function compile(DatabaseDialect $dialect): string
    {
        return $this->value;
    }
}
