<?php

declare(strict_types=1);

namespace Tempest\Console\Discovery;

use ReflectionClass;
use ReflectionMethod;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleConfig;
use Tempest\Container\Container;
use Tempest\Discovery\Discovery;
use Tempest\Discovery\HandlesDiscoveryCache;
use Tempest\Support\Reflection\Attributes;

final readonly class ConsoleCommandDiscovery implements Discovery
{
    use HandlesDiscoveryCache;

    public function __construct(
        private ConsoleConfig $consoleConfig,
    ) {
    }

    public function discover(ReflectionClass $class): void
    {
        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $consoleCommand = Attributes::find(ConsoleCommand::class)->in($method)->first();

            if (! $consoleCommand) {
                continue;
            }

            $this->consoleConfig->addCommand($method, $consoleCommand);
        }
    }

    public function createCachePayload(): string
    {
        return serialize($this->consoleConfig->commands);
    }

    public function restoreCachePayload(Container $container, string $payload): void
    {
        $commands = unserialize($payload);

        $this->consoleConfig->commands = $commands;
    }
}
