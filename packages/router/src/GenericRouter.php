<?php

declare(strict_types=1);

namespace Tempest\Router;

use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use ReflectionClass;
use Tempest\Container\Container;
use Tempest\Core\AppConfig;
use Tempest\Database\PrimaryKey;
use Tempest\Http\Mappers\PsrRequestToGenericRequestMapper;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Ok;
use Tempest\Router\Exceptions\ControllerActionHadNoReturn;
use Tempest\Router\Exceptions\MatchedRouteCouldNotBeResolved;
use Tempest\Router\Routing\Matching\RouteMatcher;
use Tempest\View\View;

use function Tempest\map;

final readonly class GenericRouter implements Router
{
    public function __construct(
        private Container $container,
        private RouteMatcher $routeMatcher,
        private AppConfig $appConfig,
        private RouteConfig $routeConfig,
    ) {}

    public function dispatch(Request|PsrRequest $request): Response
    {
        if (! $request instanceof Request) {
            $request = map($request)->with(PsrRequestToGenericRequestMapper::class)->do();
        }

        $this->container->singleton(Request::class, fn () => $request);

        $callable = $this->getCallable();

        return $this->processResponse($callable($request));
    }

    private function getCallable(): HttpMiddlewareCallable
    {
        $callControllerAction = function (Request $_) {
            $matchedRoute = $this->container->get(MatchedRoute::class);

            if ($matchedRoute === null) {
                // At this point, the `MatchRouteMiddleware` should have run.
                // If that's not the case, then someone messed up by clearing all HTTP middleware
                throw new MatchedRouteCouldNotBeResolved();
            }

            $route = $matchedRoute->route;

            $response = $this->container->invoke(
                $route->handler,
                ...$matchedRoute->params,
            );

            if ($response === null) {
                throw new ControllerActionHadNoReturn($route);
            }

            return $response;
        };

        $callable = new HttpMiddlewareCallable(fn (Request $request) => $this->createResponse($callControllerAction($request)));

        $middlewareStack = $this->routeConfig->middleware;

        foreach ($middlewareStack->unwrap() as $middlewareClass) {
            $callable = new HttpMiddlewareCallable(function (Request $request) use ($middlewareClass, $callable) {
                // We skip this middleware if it's ignored by the route
                if ($this->container->has(MatchedRoute::class)) {
                    $matchedRoute = $this->container->get(MatchedRoute::class);

                    if (in_array($middlewareClass->getName(), $matchedRoute->route->without, strict: true)) {
                        return $callable($request);
                    }
                }

                /** @var HttpMiddleware $middleware */
                $middleware = $this->container->get($middlewareClass->getName());

                return $middleware($request, $callable);
            });
        }

        return $callable;
    }

    private function createResponse(string|array|Response|View $input): Response
    {
        if ($input instanceof View || is_array($input) || is_string($input)) {
            return new Ok($input);
        }

        return $input;
    }

    private function processResponse(Response $response): Response
    {
        foreach ($this->routeConfig->responseProcessors as $responseProcessorClass) {
            /** @var \Tempest\Router\ResponseProcessor $responseProcessor */
            $responseProcessor = $this->container->get($responseProcessorClass);

            $response = $responseProcessor->process($response);
        }

        return $response;
    }
}
