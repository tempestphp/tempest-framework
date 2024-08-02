<?php

declare(strict_types=1);

namespace Tempest\EventBus;

use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use Tempest\Container\Container;
use Tempest\Discovery\Discovery;
use Tempest\Discovery\HandlesDiscoveryCache;
use Tempest\Support\Reflection\Attributes;

final readonly class EventBusDiscovery implements Discovery
{
    use HandlesDiscoveryCache;

    public function __construct(
        private EventBusConfig $eventBusConfig,
    ) {
    }

    public function discover(ReflectionClass $class): void
    {
        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $eventHandler = Attributes::find(EventHandler::class)->in($method)->first();

            if (! $eventHandler) {
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
        $handlers = unserialize($payload);

        $this->eventBusConfig->handlers = $handlers;
    }
}
