<?php

declare(strict_types=1);

namespace Tempest\Testing\Http;

use Exception;
use Tempest\Container\Container;
use Tempest\Http\GenericRequest;
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
        /** @var \Tempest\Http\Router $router */
        $router = $this->container->get(Router::class);

        // Let's check whether the current request matches a route
        $matchedRoute = $router->matchRoute($request);

        // If not, there's nothing left to do, we can't send this request
        if (! $matchedRoute) {
            throw new Exception("No matching route found for {$request->getMethod()->value} {$request->getPath()}");
        }

        // If we have a match, let's find out if our input request data matches what the route's action needs
        $requestClass = $request::class;

        // We'll loop over all the handler's parameters
        foreach ($matchedRoute->route->handler->getParameters() as $parameter) {
            // TODO: support unions

            // If the parameter's type is an instance of Request…
            if (is_a($parameter->getType()->getName(), Request::class, true)) {
                // We'll use that specific request class
                $requestClass = $parameter->getType()->getName();

                break;
            }
        }

        // We map the original request we got into this method to the right request class
        $request = map($request)->to($requestClass);

        // Finally, we register this newly created request object in the container
        // This makes it so that RequestInitializer is bypassed entirely when the controller action needs the request class
        // Making it so that we don't need to set any $_SERVER variables and stuff like that
        $this->container->singleton(Request::class, fn () => $request);
        $this->container->singleton($request::class, fn () => $request);

        // Ok, now finally for real, we dispatch our request and return the response
        return new TestResponseHelper($router->dispatch($request));
    }
}
