<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

enum AlterStatement: string
{
    case ADD = 'ADD';
    case DROP = 'DROP';
    case DELETE = 'DELETE';
    case UPDATE = 'UPDATE';
    case REPLACE = 'REPLACE';
    case RENAME = 'RENAME';
}
