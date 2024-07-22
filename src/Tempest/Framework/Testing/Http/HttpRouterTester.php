<?php

declare(strict_types=1);

namespace Tempest\Framework\Testing\Http;

use Laminas\Diactoros\ServerRequestFactory;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Tempest\Container\Container;
use Tempest\Http\GenericRequest;
use Tempest\Http\Mappers\RequestToPsrRequestMapper;
use Tempest\Http\Method;
use Tempest\Http\Request;
use Tempest\Http\Router;
use function Tempest\map;

final class HttpRouterTester
{
    public function __construct(private Container $container)
    {
    }

    public function get(string $uri, array $headers = []): TestResponseHelper
    {
        return $this->sendRequest(
            new GenericRequest(
                method: Method::GET,
                uri: $uri,
                body: [],
                headers: $headers,
            ),
        );
    }

    public function post(string $uri, array $body = [], array $headers = []): TestResponseHelper
    {
        return $this->sendRequest(
            new GenericRequest(
                method: Method::POST,
                uri: $uri,
                body: $body,
                headers: $headers,
            ),
        );
    }

    public function sendRequest(Request $request): TestResponseHelper
    {
        /** @var Router $router */
        $router = $this->container->get(Router::class);

        return new TestResponseHelper(
            $router->dispatch(map($request)->with(RequestToPsrRequestMapper::class)),
        );
    }

    public function makePsrRequest(
        string $uri,
        Method $method = Method::GET,
        array $body = [],
        array $headers = [],
        array $cookies = [],
    ): PsrRequest {
        $_SERVER['REQUEST_URI'] = $uri;
        $_SERVER['REQUEST_METHOD'] = $method->value;

        foreach ($headers as $key => $value) {
            $key = strtoupper($key);

            $_SERVER["HTTP_{$key}"] = $value;
        }

        $_COOKIE = $cookies;
        $_POST = $body;

        return ServerRequestFactory::fromGlobals();
    }
}
