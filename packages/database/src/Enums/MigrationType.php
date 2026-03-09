<?php

declare(strict_types=1);

namespace Tempest\Database\Enums;

/**
 * Used by the `make:migration` command to differentiate the type of migration to be created.
 */
enum MigrationType: string
{
    case OBJECT = 'class';
    case RAW = 'raw';
    case UP = 'up';
}
