<?php

declare(strict_types=1);

namespace Tempest\Log;

use Psr\Log\LoggerInterface;
use Tempest\Container\Container;
use Tempest\Container\DynamicInitializer;
use Tempest\Container\Singleton;
use Tempest\Core\Environment;
use Tempest\EventBus\EventBus;
use Tempest\Reflection\ClassReflector;
use UnitEnum;

final readonly class LoggerInitializer implements DynamicInitializer
{
    public function canInitialize(ClassReflector $class, string|UnitEnum|null $tag): bool
    {
        return $class->getType()->matches(Logger::class) || $class->getType()->matches(LoggerInterface::class);
    }

    #[Singleton]
    public function initialize(ClassReflector $class, string|UnitEnum|null $tag, Container $container): LoggerInterface|Logger
    {
        return new GenericLogger(
            logConfig: $container->get(LogConfig::class, $tag),
            environment: $container->get(Environment::class),
            eventBus: $container->get(EventBus::class),
        );
    }
}
