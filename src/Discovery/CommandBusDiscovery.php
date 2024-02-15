<?php

declare(strict_types=1);

namespace Tempest\Discovery;

use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use Tempest\Bus\CommandBusConfig;
use Tempest\Interface\Command;
use Tempest\Interface\Container;
use Tempest\Interface\Discovery;

final readonly class CommandBusDiscovery implements Discovery
{
    private const CACHE_PATH = __DIR__ . '/command-bus-discovery.cache.php';

    public function __construct(
        private CommandBusConfig $commandBusConfig,
    ) {
    }

    public function discover(ReflectionClass $class): void
    {
        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->isConstructor() || $method->isDestructor()) {
                continue;
            }

            $parameters = $method->getParameters();

            if (count($parameters) !== 1) {
                continue;
            }

            $firstParameter = $parameters[0];

            $type = $firstParameter->getType();

            if (! $type instanceof ReflectionNamedType) {
                continue;
            }

            if (! class_exists($type->getName())) {
                continue;
            }

            $typeClass = new ReflectionClass($type->getName());

            if (! $typeClass->implementsInterface(Command::class)) {
                continue;
            }

            $this->commandBusConfig->addHandler($typeClass->getName(), $method);
        }
    }

    public function hasCache(): bool
    {
        return file_exists(self::CACHE_PATH);
    }

    public function storeCache(): void
    {
        file_put_contents(self::CACHE_PATH, serialize($this->commandBusConfig->handlers));
    }

    public function restoreCache(Container $container): void
    {
        $handlers = unserialize(file_get_contents(self::CACHE_PATH));

        $this->commandBusConfig->handlers = $handlers;
    }

    public function destroyCache(): void
    {
        unlink(self::CACHE_PATH);
    }
}
