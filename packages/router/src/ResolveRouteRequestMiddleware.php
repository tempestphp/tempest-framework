<?php

declare(strict_types=1);

namespace Tempest\Router;

use Tempest\Container\Container;
use Tempest\Http\GenericRequest;
use Tempest\Http\Mappers\RequestToObjectMapper;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Support\Priority;

use function Tempest\Mapper\map;

#[Priority(Priority::FRAMEWORK - 9)]
final readonly class ResolveRouteRequestMiddleware implements HttpMiddleware
{
    public function __construct(
        private MatchedRoute $matchedRoute,
        private Container $container,
    ) {}

    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        $request = $this->resolveRequest($request);

        // We register this newly created request object in the container
        // This makes it so that RequestInitializer is bypassed entirely when the controller action needs the request class
        // Making it so that we don't need to set any $_SERVER variables and stuff like that
        $this->container->singleton($request::class, fn () => $request);

        return $next($request);
    }

    private function resolveRequest(Request $request): Request
    {
        // Let's find out if our input request data matches what the route's action needs
        $requestClass = GenericRequest::class;

        // We'll loop over all the handler's parameters
        foreach ($this->matchedRoute
            ->route
            ->handler
            ->getParameters() as $parameter) {
            // If the parameter's type is an instance of Request…
            if (! $parameter->getType()->matches(Request::class)) {
                continue;
            }

            $requestClass = $parameter->getType()->getName();

            break;
        }

        if ($requestClass !== Request::class && $requestClass !== GenericRequest::class) {
            return map($request)->with(RequestToObjectMapper::class)->to($requestClass);
        }

        return $request;
    }
}
