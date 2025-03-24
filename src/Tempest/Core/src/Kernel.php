<?php

declare(strict_types=1);

namespace Tempest\Core;

use Dotenv\Dotenv;
use Tempest\Console\Exceptions\ConsoleErrorHandler;
use Tempest\Container\Container;
use Tempest\Container\GenericContainer;
use Tempest\Core\Kernel\FinishDeferredTasks;
use Tempest\Core\Kernel\LoadConfig;
use Tempest\Core\Kernel\LoadDiscoveryClasses;
use Tempest\Core\Kernel\LoadDiscoveryLocations;
use Tempest\Core\ShellExecutors\GenericShellExecutor;
use Tempest\EventBus\EventBus;
use Tempest\Router\Exceptions\HttpProductionErrorHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

interface Kernel
{
    public const string VERSION = '1.0.0-alpha.6';

    public string $root {
        get;
    }

    public string $internalStorage {
        get;
    }

    public array $discoveryLocations {
        get;
        set;
    }

    public array $discoveryClasses {
        get;
        set;
    }

    public Container $container {
        get;
    }

    public static function boot(
        string $root,
        array $discoveryLocations = [],
        ?Container $container = null,
    ): self;

    public function shutdown(int|string $status = ''): never;
}
