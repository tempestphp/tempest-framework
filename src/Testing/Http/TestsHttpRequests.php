<?php

declare(strict_types=1);

namespace Tempest\Testing\Http;

use Tempest\Http\GenericRequest;
use Tempest\Http\Method;
use Tempest\Http\Request;
use Tempest\Http\ResponseSender;
use Tempest\Http\Router;

trait TestsHttpRequests
{
    protected function get(string $path): TestResponse
    {
        return $this->performRequest(
            new GenericRequest(
                method: Method::GET,
                uri: $path,
                body: [],
                headers: [],
            )
        );
    }

    protected function post(string $path): TestResponse
    {
        return $this->performRequest(
            new GenericRequest(
                method: Method::POST,
                uri: $path,
                body: [],
                headers: [],
            )
        );
    }

    private function performRequest(Request $request): TestResponse
    {
        // Register our test request in the container.
        $this->container->singleton(Request::class, fn () => $request);
        $this->container->singleton(ResponseSender::class, fn () => new TestResponseSender());

        $router = $this->container->get(Router::class);
        $responseSender = $this->container->get(ResponseSender::class);

        return $responseSender->send(
            $router->dispatch($request)
        );
    }
}
