<?php

namespace Tempest\Router;

use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Tempest\Container\Container;
use Tempest\Core\Priority;
use Tempest\Http\GenericRequest;
use Tempest\Http\Mappers\PsrRequestToGenericRequestMapper;
use Tempest\Http\Mappers\RequestToObjectMapper;
use Tempest\Http\Mappers\RequestToPsrRequestMapper;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\NotFound;
use Tempest\Router\Routing\Matching\RouteMatcher;
use function Tempest\map;

#[Priority(Priority::FRAMEWORK - 10)]
final class MatchRouteMiddleware  implements HttpMiddleware
{
    public function __construct(
        private RouteMatcher $routeMatcher,
        private Container $container,
    ) {}

    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        $psrRequest = map($request)->with(RequestToPsrRequestMapper::class)->do();

        $matchedRoute = $this->routeMatcher->match($psrRequest);

        if ($matchedRoute === null) {
            return new NotFound();
        }

        $this->container->singleton(MatchedRoute::class, fn () => $matchedRoute);

        $request = $this->resolveRequest($psrRequest, $matchedRoute);

        return $next($request);
    }

    private function resolveRequest(PsrRequest $psrRequest, MatchedRoute $matchedRoute): Request
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

        // We map the original request we got into this method to the right request class
        /** @var \Tempest\Http\GenericRequest $request */
        $request = map($psrRequest)->with(PsrRequestToGenericRequestMapper::class)->do();

        if ($requestClass !== Request::class && $requestClass !== GenericRequest::class) {
            $request = map($request)->with(RequestToObjectMapper::class)->to($requestClass);
        }

        // Next, we register this newly created request object in the container
        // This makes it so that RequestInitializer is bypassed entirely when the controller action needs the request class
        // Making it so that we don't need to set any $_SERVER variables and stuff like that
        $this->container->singleton(Request::class, fn () => $request);
        $this->container->singleton($request::class, fn () => $request);

        return $request;
    }
}