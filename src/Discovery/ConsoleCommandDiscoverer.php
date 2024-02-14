<?php

declare(strict_types=1);

namespace Tempest\Discovery;

use ReflectionClass;
use ReflectionMethod;

use function Tempest\attribute;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleConfig;
use Tempest\Interface\Discoverer;

final readonly class ConsoleCommandDiscoverer implements Discoverer
{
    public function __construct(private ConsoleConfig $config)
    {
    }

    public function discover(ReflectionClass $class): void
    {
        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $consoleCommand = attribute(ConsoleCommand::class)->in($method)->first();

            if (! $consoleCommand) {
                continue;
            }

            $this->config->addCommand($method, $consoleCommand);
        }
    }
}
