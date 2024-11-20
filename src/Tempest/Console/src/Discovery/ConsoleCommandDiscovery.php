<?php

declare(strict_types=1);

namespace Tempest\Console\Discovery;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleConfig;
use Tempest\Core\Discovery;
use Tempest\Core\DiscoveryLocation;
use Tempest\Core\IsDiscovery;
use Tempest\Reflection\ClassReflector;

final readonly class ConsoleCommandDiscovery implements Discovery
{
    use IsDiscovery;

    public function __construct(
        private ConsoleConfig $consoleConfig,
    ) {}

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        foreach ($class->getPublicMethods() as $method) {
            $consoleCommand = $method->getAttribute(ConsoleCommand::class);

            if (! $consoleCommand) {
                continue;
            }

            $this->discoveryItems->add($location, [$method, $consoleCommand]);
        }
    }

    public function apply(): void
    {
        foreach ($this->discoveryItems->flatten() as [$method, $consoleCommand]) {
            $this->consoleConfig->addCommand($method, $consoleCommand);
        }
    }
}
