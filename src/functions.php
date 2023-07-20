<?php

declare(strict_types=1);

namespace Tempest {

    use Tempest\Container\GenericContainer;
    use Tempest\Http\GenericRequest;
    use Tempest\Http\GenericResponse;
    use Tempest\Http\Method;
    use Tempest\Http\Status;
    use Tempest\Interfaces\Request;
    use Tempest\Interfaces\Response;
    use Tempest\Interfaces\Router;
    use Tempest\Interfaces\View;
    use Tempest\Mappers\Mapper;
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
     * @return Mapper<T>
     */
    function make(object|string $objectOrClass): Mapper
    {
        $factory = get(Mapper::class);

        return $factory->forClass($objectOrClass);
    }

    function map(mixed $data): Mapper
    {
        $factory = get(Mapper::class);

        return $factory->withData($data);
    }
}
