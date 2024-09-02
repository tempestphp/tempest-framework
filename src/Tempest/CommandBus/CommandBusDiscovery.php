<?php

declare(strict_types=1);

namespace Tempest\CommandBus;

use Tempest\Container\Container;
use Tempest\Core\Discovery;
use Tempest\Core\HandlesDiscoveryCache;
use Tempest\Support\Reflection\ClassReflector;
use Tempest\Support\Reflection\MethodReflector;

final readonly class CommandBusDiscovery implements Discovery
{
    use HandlesDiscoveryCache;

    public function __construct(
        private CommandBusConfig $commandBusConfig,
    ) {
    }

    public function discover(ClassReflector $class): void
    {
        foreach ($class->getPublicMethods() as $method) {
            $commandHandler = $method->getAttribute(CommandHandler::class);

            if (! $commandHandler) {
                continue;
            }

            $parameters = iterator_to_array($method->getParameters());

            if (count($parameters) !== 1) {
                continue;
            }

            $type = $parameters[0]->getType();

            if (! $type->isClass()) {
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
        $handlers = unserialize($payload, ['allowed_classes' => [CommandHandler::class, MethodReflector::class]]);

        $this->commandBusConfig->handlers = $handlers;
    }
}
