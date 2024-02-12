<?php

declare(strict_types=1);

namespace Tempest\Discovery;

use ReflectionClass;
use ReflectionMethod;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleConfig;
use Tempest\Interface\Discoverer;
use function Tempest\attribute;

final readonly class ConsoleCommandDiscoverer implements Discoverer
{
    public function __construct(private ConsoleConfig $config)
    {
    }

    public function discover(ReflectionClass $class): void
    {
        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $attributes = attribute(ConsoleCommand::class)->in($method)->all();

            if ($attributes !== []) {
                $this->config->addCommand($method);
            }
        }
    }
}
