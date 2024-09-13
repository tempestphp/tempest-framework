<?php

declare(strict_types=1);

namespace Tempest\Console\Discovery;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleConfig;
use Tempest\Container\Container;
use Tempest\Core\Discovery;
use Tempest\Core\HandlesDiscoveryCache;
use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\MethodReflector;

final readonly class ConsoleCommandDiscovery implements Discovery
{
    use HandlesDiscoveryCache;

    public function __construct(
        private ConsoleConfig $consoleConfig,
    ) {
    }

    public function discover(ClassReflector $class): void
    {
        foreach ($class->getPublicMethods() as $method) {
            $consoleCommand = $method->getAttribute(ConsoleCommand::class);

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
        $commands = unserialize($payload, ['allowed_classes' => [ConsoleCommand::class, MethodReflector::class]]);

        $this->consoleConfig->commands = $commands;
    }
}
