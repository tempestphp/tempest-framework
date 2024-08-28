<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\DatabaseDialect;
use Tempest\Database\QueryStatement;

enum Alter: string implements QueryStatement
{
    case ADD = 'ADD';
    case DROP = 'DROP';
    case DELETE = 'DELETE';
    case UPDATE = 'UPDATE';
    case REPLACE = 'REPLACE';
    case RENAME = 'RENAME';

    public function compile(DatabaseDialect $dialect): string
    {
        return match ($dialect) {
            DatabaseDialect::MYSQL => $this->value,
            DatabaseDialect::POSTGRESQL,
            DatabaseDialect::SQLITE => $this->value . ' COLUMN',
        };
    }
}
