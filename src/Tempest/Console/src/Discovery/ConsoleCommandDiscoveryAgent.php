<?php

namespace Tempest\Console\Discovery;

use ReflectionClass;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleConfig;
use Tempest\Container\Discovery\Agent\DiscoveryAgent;
use Tempest\Reflection\ClassReflector;

final class ConsoleCommandDiscoveryAgent implements DiscoveryAgent
{
    public function __construct(
        private readonly ConsoleConfig $consoleConfig,
    ) {
    }

    public function inspect(ReflectionClass $class): void
    {
        $class = new ClassReflector($class);

        foreach ($class->getPublicMethods() as $method) {
            $consoleCommand = $method->getAttribute(ConsoleCommand::class);

            if (! $consoleCommand) {
                continue;
            }

            $this->consoleConfig->addCommand($method, $consoleCommand);
        }
    }
}