<?php

declare(strict_types=1);

namespace Tempest\CommandBus;

use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use function Tempest\attribute;
use Tempest\Container\Container;
use Tempest\Discovery\Discovery;

final readonly class CommandBusDiscovery implements Discovery
{
    private const string CACHE_PATH = __DIR__ . '/../../../.cache/tempest/command-bus-discovery.cache.php';

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
        @unlink(self::CACHE_PATH);
    }
}
