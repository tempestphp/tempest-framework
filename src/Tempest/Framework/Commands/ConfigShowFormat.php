<?php

declare(strict_types=1);

namespace Tempest\Framework\Commands;

/**
 * @internal
 */
enum ConfigShowFormat: string
{
    case DUMP = 'dump';
    case PRETTY = 'pretty';
    case FILE = 'file';
}
