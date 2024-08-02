<?php

declare(strict_types=1);

namespace Tempest\CommandBus;

use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use function Tempest\attribute;
use Tempest\Container\Container;
use Tempest\Discovery\Discovery;
use Tempest\Discovery\HandlesDiscoveryCache;

final readonly class CommandBusDiscovery implements Discovery
{
    use HandlesDiscoveryCache;

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

    public function createCachePayload(): string
    {
        return serialize($this->commandBusConfig->handlers);
    }

    public function restoreCachePayload(Container $container, string $payload): void
    {
        $handlers = unserialize($payload);

        $this->commandBusConfig->handlers = $handlers;
    }
}
