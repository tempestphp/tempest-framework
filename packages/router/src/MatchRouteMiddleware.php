<?php

namespace Tempest\Router;

use Tempest\Container\Container;
use Tempest\Core\Priority;
use Tempest\Http\GenericRequest;
use Tempest\Http\Mappers\RequestToObjectMapper;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\NotFound;
use Tempest\Router\Routing\Matching\RouteMatcher;

use function Tempest\map;

#[Priority(Priority::FRAMEWORK - 9)]
final readonly class MatchRouteMiddleware implements HttpMiddleware
{
    public function __construct(
        private RouteMatcher $routeMatcher,
        private Container $container,
    ) {}

    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        $matchedRoute = $this->routeMatcher->match($request);

        if ($matchedRoute === null) {
            return new NotFound();
        }

        // We register the matched route in the container, some internal framework components will need it
        $this->container->singleton(MatchedRoute::class, fn () => $matchedRoute);

        // Convert the request to a specific request implementation, if needed
        $request = $this->resolveRequest($request, $matchedRoute);

        // We register this newly created request object in the container
        // This makes it so that RequestInitializer is bypassed entirely when the controller action needs the request class
        // Making it so that we don't need to set any $_SERVER variables and stuff like that
        $this->container->singleton(Request::class, fn () => $request);
        $this->container->singleton($request::class, fn () => $request);

        return $next($request);
    }

    private function resolveRequest(Request $request, MatchedRoute $matchedRoute): Request
    {
        // Let's find out if our input request data matches what the route's action needs
        $requestClass = GenericRequest::class;

        // We'll loop over all the handler's parameters
        foreach ($matchedRoute->route->handler->getParameters() as $parameter) {
            // If the parameter's type is an instance of Request…
            if ($parameter->getType()->matches(Request::class)) {
                // We'll use that specific request class
                $requestClass = $parameter->getType()->getName();

                break;
            }
        }

        if ($requestClass !== Request::class && $requestClass !== GenericRequest::class) {
            $request = map($request)->with(RequestToObjectMapper::class)->to($requestClass);
        }

        return $request;
    }
}
