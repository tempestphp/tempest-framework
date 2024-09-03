<?php

declare(strict_types=1);

namespace Tempest {

    use ReflectionClass as PHPReflectionClass;
    use ReflectionProperty as PHPReflectionProperty;
    use Tempest\CommandBus\CommandBus;
    use Tempest\Container\GenericContainer;
    use Tempest\EventBus\EventBus;
    use Tempest\Support\Reflection\ClassReflector;
    use Tempest\Support\Reflection\PropertyReflector;

    /**
     * @template TClassName of object
     * @param class-string<TClassName> $className
     * @return TClassName
     */
    function get(string $className, ?string $tag = null, mixed ...$params): object
    {
        $container = GenericContainer::instance();

        return $container->get($className, $tag, ...$params);
    }

    function event(object $event): void
    {
        $eventBus = get(EventBus::class);

        $eventBus->dispatch($event);
    }

    function path(string ...$parts): string
    {
        $path = implode('/', $parts);

        return str_replace(
            ['///', '//', '\\', '\\\\'],
            '/',
            $path,
        );
    }

    function command(object $command): void
    {
        $commandBus = get(CommandBus::class);

        $commandBus->dispatch($command);
    }

    function reflect(mixed $classOrProperty, ?string $propertyName = null): ClassReflector|PropertyReflector
    {
        if ($classOrProperty instanceof PHPReflectionClass) {
            return new ClassReflector($classOrProperty);
        }

        if ($classOrProperty instanceof PHPReflectionProperty) {
            return new PropertyReflector($classOrProperty);
        }

        if ($propertyName !== null) {
            return new PropertyReflector(new PHPReflectionProperty($classOrProperty, $propertyName));
        }

        return new ClassReflector($classOrProperty);
    }
}
