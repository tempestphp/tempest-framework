<?php

declare(strict_types=1);

namespace Tempest\Discovery;

use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use function Tempest\attribute;
use Tempest\Commands\CommandBusConfig;
use Tempest\Commands\CommandHandler;

final readonly class CommandBusDiscovery implements Discoverer, CacheableDiscoverer
{
    public function __construct(
        private CommandBusConfig $commandBusConfig,
    ) {
    }

    public function discover(ReflectionClass $class): void
    {
        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $commandHandler = attribute(CommandHandler::class)->in($method)->first();

            if (! $commandHandler) {
                continue;
            }

            $parameters = $method->getParameters();

            if (count($parameters) !== 1) {
                continue;
            }

            $type = $parameters[0]->getType();

            if (! $type instanceof ReflectionNamedType) {
                continue;
            }

            if (! class_exists($type->getName())) {
                continue;
            }

            $this->commandBusConfig->addHandler(
                commandHandler: $commandHandler,
                commandName: $type->getName(),
                handler: $method,
            );
        }
    }

    public function getResults(): array
    {
        return $this->commandBusConfig->handlers;
    }

    public function restoreResults(array $classes): void
    {
        $this->commandBusConfig->handlers = $classes;
    }
}
