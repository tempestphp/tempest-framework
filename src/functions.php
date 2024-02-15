<?php

declare(strict_types=1);

namespace Tempest {

    use Tempest\Container\GenericContainer;
    use Tempest\Http\GenericRequest;
    use Tempest\Http\GenericResponse;
    use Tempest\Http\Method;
    use Tempest\Http\Status;
    use Tempest\Interface\CommandBus;
    use Tempest\Interface\Request;
    use Tempest\Interface\Response;
    use Tempest\Interface\Router;
    use Tempest\Interface\View;
    use Tempest\Mapper\ObjectMapper;
    use Tempest\Support\Reflection\Attributes;
    use Tempest\View\GenericView;

    /**
     * @template TClassName
     * @param class-string<TClassName> $className
     * @return TClassName
     */
    function get(string $className): object
    {
        $container = GenericContainer::instance();

        return $container->get($className);
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

    function request(string $uri, array $body = []): Request
    {
        return new GenericRequest(Method::GET, $uri, $body);
    }

    function response(string $body = ''): Response
    {
        return new GenericResponse(Status::HTTP_200, $body);
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
        return response()->redirect(uri($action, ...$params));
    }

    /**
     * @template T of object
     * @param T|class-string<T> $objectOrClass
     * @return ObjectMapper<T>
     */
    function make(object|string $objectOrClass): ObjectMapper
    {
        $factory = get(ObjectMapper::class);

        return $factory->forClass($objectOrClass);
    }

    function map(mixed $data): ObjectMapper
    {
        $factory = get(ObjectMapper::class);

        return $factory->withData($data);
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

    function command(object $command): void
    {
        $commandBus = get(CommandBus::class);

        $commandBus->dispatch($command);
    }

    function env($key, $default = null): mixed
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
