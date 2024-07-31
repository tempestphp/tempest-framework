<?php

declare(strict_types=1);

namespace Tempest\EventBus;

use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use Tempest\Container\Container;
use Tempest\Discovery\Discovery;
use Tempest\Support\Reflection\Attributes;

final readonly class EventBusDiscovery implements Discovery
{
    private const string CACHE_PATH = __DIR__ . '/../../../.cache/tempest/event-bus-discovery.cache.php';

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

    public function hasCache(): bool
    {
        return file_exists(self::CACHE_PATH);
    }

    public function storeCache(): void
    {
        file_put_contents(self::CACHE_PATH, serialize($this->eventBusConfig->handlers));
    }

    public function restoreCache(Container $container): void
    {
        $handlers = unserialize(file_get_contents(self::CACHE_PATH));

        $this->eventBusConfig->handlers = $handlers;
    }

    public function destroyCache(): void
    {
        @unlink(self::CACHE_PATH);
    }
}
