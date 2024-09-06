<?php

declare(strict_types=1);

namespace Tempest\Console\Initializers;

use Tempest\Console\ConsoleConfig;
use Tempest\Console\Output\LogOutputBuffer;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Core\AppConfig;
use Tempest\Core\Kernel;
use Tempest\Support\PathHelper;

final readonly class LogOutputBufferInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): LogOutputBuffer
    {
        $consoleConfig = $container->get(ConsoleConfig::class);
        $kernel = $container->get(Kernel::class);

        $path = $consoleConfig->logPath ?? PathHelper::make($kernel->root, 'console.log');

        return new LogOutputBuffer($path);
    }
}
