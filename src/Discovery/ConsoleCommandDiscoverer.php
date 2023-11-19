<?php

declare(strict_types=1);

namespace Tempest\Discovery;

use ReflectionClass;
use Tempest\Console\ConsoleConfig;
use Tempest\Interface\ConsoleCommand;
use Tempest\Interface\Discoverer;

final readonly class ConsoleCommandDiscoverer implements Discoverer
{
    public function __construct(private ConsoleConfig $config)
    {
    }

    public function discover(ReflectionClass $class): void
    {
        if (! $class->implementsInterface(ConsoleCommand::class)) {
            return;
        }

        foreach ($class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            $this->config->addCommand($method);
        }
    }
}
