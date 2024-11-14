<?php

declare(strict_types=1);

namespace Tempest\Framework\Commands;

/**
 * @internal
 */
enum ConfigStatusFormat: string
{
    case DUMP = 'dump';
    case JSON = 'json';
    case FILE = 'file';
}
