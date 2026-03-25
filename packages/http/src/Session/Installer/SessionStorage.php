<?php

declare(strict_types=1);

namespace Tempest\Http\Session\Installer;

enum SessionStorage: string
{
    case FILE = 'file';
    case DATABASE = 'database';
    case REDIS = 'redis';
}
