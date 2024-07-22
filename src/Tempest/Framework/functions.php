<?php

declare(strict_types=1);

namespace {
    use function Tempest\get;
    use Tempest\Support\VarExport\Debug;

    if (! function_exists('lw')) {
        function lw(mixed ...$input): void
        {
            get(Debug::class)->log($input);
        }
    }

    if (! function_exists('ld')) {
        function ld(mixed ...$input): void
        {
            get(Debug::class)->log($input);
            die();
        }
    }

    if (! function_exists('ll')) {
        function ll(mixed ...$input): void
        {
            get(Debug::class)->log($input, writeToOut: false);
        }
    }
}

namespace Tempest {

    use ReflectionType;
    use Reflector;
    use Tempest\CommandBus\CommandBus;
    use Tempest\Container\GenericContainer;
    use Tempest\EventBus\EventBus;
    use Tempest\Http\GenericResponse;
    use Tempest\Http\Response;
    use Tempest\Http\Responses\Redirect;

    use Tempest\Http\Router;
    use Tempest\Http\Status;
    use Tempest\Mapper\ObjectFactory;
    use Tempest\Support\Reflection\Attributes;
    use Tempest\Support\Reflection\TypeName;
    use Tempest\View\GenericView;
    use Tempest\View\View;

    /**
     * @template TClassName of object
     * @param class-string<TClassName> $className
     * @param string|null $tag
     * @param mixed ...$params
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
     * @param class-string<T> $attributeName
     * @return \Tempest\Support\Reflection\Attributes<T>
     */
    function attribute(string $attributeName): Attributes
    {
        return Attributes::find($attributeName);
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

    function type(Reflector|ReflectionType $reflector): string
    {
        return (new TypeName())->resolve($reflector);
    }

    function path(string ...$parts): string
    {
        $path = implode('/', $parts);

        return str_replace(
            ['//', '\\', '\\\\'],
            ['/', '/', '/'],
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

    function uri(array|string $action, ...$params): string
    {
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
}
