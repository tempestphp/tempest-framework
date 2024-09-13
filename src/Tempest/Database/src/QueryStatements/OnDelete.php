<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

enum OnDelete: string
{
    case CASCADE = 'CASCADE';
    case SET_NULL = 'SET NULL';
    case RESTRICT = 'RESTRICT';
    case NO_ACTION = 'NO ACTION';
    case SET_DEFAULT = 'SET DEFAULT';
}
