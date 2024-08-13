<?php

declare(strict_types=1);

namespace Tempest\EventBus;

use Tempest\Container\Container;
use Tempest\Discovery\Discovery;
use Tempest\Discovery\HandlesDiscoveryCache;
use Tempest\Support\Reflection\ClassReflector;
use Tempest\Support\Reflection\MethodReflector;

final readonly class EventBusDiscovery implements Discovery
{
    use HandlesDiscoveryCache;

    public function __construct(
        private EventBusConfig $eventBusConfig,
    ) {
    }

    public function discover(ClassReflector $class): void
    {
        foreach ($class->getPublicMethods() as $method) {
            $eventHandler = $method->getAttribute(EventHandler::class);

            if (! $eventHandler) {
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

            $this->eventBusConfig->addHandler(
                eventHandler: $eventHandler,
                eventName: $type->getName(),
                reflectionMethod: $method,
            );
        }
    }

    public function createCachePayload(): string
    {
        return serialize($this->eventBusConfig->handlers);
    }

    public function restoreCachePayload(Container $container, string $payload): void
    {
        $handlers = unserialize($payload, ['allowed_classes' => [EventHandler::class, MethodReflector::class]]);

        $this->eventBusConfig->handlers = $handlers;
    }
}
