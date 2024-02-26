<?php

namespace Tempest\Testing\Http;

use Tempest\Container\Container;
use Tempest\Http\GenericRequest;
use Tempest\Http\Method;
use Tempest\Http\Request;
use Tempest\Http\Router;

final class HttpRouterTester
{
    public function __construct(private Container $container)
    {}

    public function get(string $path, array $headers = []): TestResponseHelper
    {
        return $this->performRequest(
            new GenericRequest(
                method: Method::GET,
                uri: $path,
                body: []
            ),
            $headers
        );
    }

    public function post(string $path, array $headers = []): TestResponseHelper
    {
        return $this->performRequest(
            new GenericRequest(
                method: Method::POST,
                uri: $path,
                body: []
            ),
            $headers
        );
    }

    private function performRequest(Request $request, array $headers): TestResponseHelper
    {
        // Register our headers as server variables.
        $this->registerServerVariables($headers);
        $this->registerRequestMethod($request);

        // Register our test request in the container.
        $this->container->singleton(Request::class, fn () => $request);

        $router = $this->container->get(Router::class);

        return new TestResponseHelper(
            $router->dispatch($request)
        );
    }

    private function registerServerVariables(array $headers): void
    {
        foreach ($headers as $name => $value) {
            $this->registerServerVariable($name, $value);
        }
    }

    private function registerRequestMethod(Request $request): void
    {
        $_SERVER['REQUEST_METHOD'] = $request->getMethod()->value;
    }

    /**
     * Somewhat based on Laravel's testing helper.
     *
     * @see https://github.com/laravel/framework/blob/10.x/src/Illuminate/Foundation/Testing/Concerns/MakesHttpRequests.php#L619-L626
     */
    private function registerServerVariable(string $name, mixed $value): void
    {
        $name = strtr(strtoupper($name), '-', '_');

        if (! str_starts_with($name, 'HTTP_') && $name !== 'CONTENT_TYPE' && $name !== 'REMOTE_ADDR') {
            $name = 'HTTP_'.$name;
        }

        $_SERVER[$name] = $value;
    }
}