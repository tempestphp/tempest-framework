<?php

declare(strict_types=1);

namespace Tempest\Http;

use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Tempest\Container\Container;
use Tempest\Http\Responses\Invalid;
use function Tempest\map;
use Tempest\Validation\Exceptions\ValidationException;

final readonly class ResolveRequestMiddleware implements HttpMiddleware
{
    public function __construct(
        private readonly Container $container,
    ) {
    }

    public function __invoke(Request $request, callable $next): Response
    {
        // Let's find out if our input request data matches what the route's action needs
        try {
            $wantedRequestClass = $this->getRequestClassFromRoute($this->container->get(MatchedRoute::class));

            if ($wantedRequestClass === null) {
                return $next($request);
            }

            // If the request class is different from the one we have, we'll map it to the new one
            if ($request::class !== $wantedRequestClass) {
                /** @var Request */
                $request = map($this->container->get(PsrRequest::class))->to($wantedRequestClass);
            }

            // We'll bind the request to the container
            $this->container->singleton(Request::class, fn () => $request);
            $this->container->singleton($request::class, fn () => $request);

            // Then we'll validate the request
            $request->validate();

            return $next($request);
        } catch (ValidationException $validationException) {
            return new Invalid($request, $validationException->failingRules);
        }
    }

    /**
     * @return class-string<Request>|null
     */
    private function getRequestClassFromRoute(MatchedRoute $matchedRoute): ?string
    {
        // We'll loop over all the handler's parameters
        foreach ($matchedRoute->route->handler->getParameters() as $parameter) {

            // If the parameter's type is an instance of Request…
            if ($parameter->getType()->matches(Request::class)) {
                // We'll use that specific request class
                return $parameter->getType()->getName();
            }
        }

        return null;
    }
}
