<?php

declare(strict_types=1);

namespace Tempest\Router;

use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Tempest\Container\Container;
use Tempest\Http\Mappers\PsrRequestToGenericRequestMapper;
use Tempest\Http\OpaqueRequest;
use Tempest\Http\Request;
use Tempest\Http\RequestHolder;
use Tempest\Http\Response;
use Tempest\Http\Responses\Ok;
use Tempest\Router\Exceptions\ControllerActionHadNoReturn;
use Tempest\Router\Routing\Matching\RouteMatcher;
use Tempest\View\View;

use function Tempest\Mapper\map;

final readonly class WorkerRouter implements Router
{
    public function __construct(
        private Container $container,
        private RequestHolder $requestHolder,
        private RouteConfig $routeConfig,
        private RouteMatcher $routeMatcher,
    ) {}

    public function dispatch(Request|PsrRequest $request): Response
    {
        if (! $request instanceof Request) {
            $request = map($request)->with(PsrRequestToGenericRequestMapper::class)->do();
        }

        $this->requestHolder->setRequest($request);
        $this->container->singleton(Request::class, $this->container->get(OpaqueRequest::class));

        $callable = $this->getCallable($request);

        return $this->processResponse($callable($request));
    }

    private function getCallable(Request $request): HttpMiddlewareCallable
    {
        $matchedRoute = $this->routeMatcher->match($request);

        $callControllerAction = function (Request $_) use ($matchedRoute) {
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
            $callable = new HttpMiddlewareCallable(closure: function (Request $request) use ($middlewareClass, $callable, $matchedRoute) {
                // Skip the middleware if it's ignored by the route
                if (in_array(
                    needle: $middlewareClass->getName(),
                    haystack: $matchedRoute->route->without,
                    strict: true,
                )) {
                    return $callable($request);
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
