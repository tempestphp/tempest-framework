<?php

declare(strict_types=1);

namespace Tempest {

    use ReflectionClass as PHPReflectionClass;
    use ReflectionProperty as PHPReflectionProperty;
    use Tempest\CommandBus\CommandBus;
    use Tempest\Container\GenericContainer;
    use Tempest\EventBus\EventBus;
    use Tempest\Http\GenericResponse;
    use Tempest\Http\Response;
    use Tempest\Http\Responses\Redirect;
    use Tempest\Http\Router;
    use Tempest\Http\Status;
    use Tempest\Mapper\ObjectFactory;
    use Tempest\Support\Reflection\ClassReflector;
    use Tempest\Support\Reflection\MethodReflector;
    use Tempest\Support\Reflection\PropertyReflector;
    use Tempest\View\GenericView;
    use Tempest\View\View;

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

    /**
     * @template T of object
     * @param T|class-string<T> $objectOrClass
     * @return ObjectFactory<T>
     */
    function make(object|string $objectOrClass): ObjectFactory
    {
        $factory = get(ObjectFactory::class);

        return $factory->forClass($objectOrClass);
    }

    function map(mixed $data): ObjectFactory
    {
        $factory = get(ObjectFactory::class);

        return $factory->withData($data);
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

    function view(string $path): View
    {
        return new GenericView($path);
    }

    function response(string $body = '', Status $status = Status::OK): Response
    {
        return new GenericResponse($status, $body);
    }

    function uri(array|string|MethodReflector $action, ...$params): string
    {
        if ($action instanceof MethodReflector) {
            $action = [
                $action->getDeclaringClass()->getName(),
                $action->getName(),
            ];
        }

        $router = get(Router::class);

        return $router->toUri(
            $action,
            ...$params,
        );
    }

    function redirect(string|array $action, ...$params): Response
    {
        return new Redirect(uri($action, ...$params));
    }

    function command(object $command): void
    {
        $commandBus = get(CommandBus::class);

        $commandBus->dispatch($command);
    }

    function env(string $key, mixed $default = null): mixed
    {
        $value = getenv($key);

        if ($value === false) {
            return $default;
        }

        return match (strtolower($value)) {
            'true' => true,
            'false' => false,
            'null', '' => null,
            default => $value,
        };
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
