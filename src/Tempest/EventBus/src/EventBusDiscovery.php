<?php

declare(strict_types=1);

namespace Tempest\EventBus;

use BackedEnum;
use Tempest\Container\Container;
use Tempest\Core\Discovery;
use Tempest\Core\HandlesDiscoveryCache;
use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\MethodReflector;
use UnitEnum;

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

            $eventName = match (true) {
                $eventHandler->event instanceof BackedEnum => $eventHandler->event->value,
                $eventHandler->event instanceof UnitEnum => $eventHandler->event->name,
                is_string($eventHandler->event) => $eventHandler->event,
                default => null,
            };

            if ($eventName === null) {
                $parameters = iterator_to_array($method->getParameters());

                if ($parameters === []) {
                    continue;
                }

                $type = $parameters[0]->getType();

                if (! $type->isClass()) {
                    continue;
                }

                $eventName = $type->getName();
            }

            $this->eventBusConfig->addClassMethodHandler(
                event: $eventName,
                handler: $eventHandler,
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
        $handlers = unserialize($payload, ['allowed_classes' => [CallableEventHandler::class, EventHandler::class, MethodReflector::class]]);

        $this->eventBusConfig->handlers = $handlers;
    }
}
