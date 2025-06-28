<?php

declare(strict_types=1);

namespace Tempest\Router;

use BackedEnum;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Tempest\Container\Container;
use Tempest\Core\AppConfig;
use Tempest\Http\Mappers\PsrRequestToGenericRequestMapper;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Ok;
use Tempest\Reflection\ClassReflector;
use Tempest\Router\Exceptions\ControllerActionHadNoReturn;
use Tempest\Router\Exceptions\ControllerMethodHadNoRouteAttribute;
use Tempest\Router\Exceptions\MatchedRouteCouldNotBeResolved;
use Tempest\Router\Routing\Construction\DiscoveredRoute;
use Tempest\Router\Routing\Matching\RouteMatcher;
use Tempest\View\View;

use function Tempest\map;
use function Tempest\Support\Regex\replace;
use function Tempest\Support\str;

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
        if (! ($request instanceof Request)) {
            $request = map($request)->with(PsrRequestToGenericRequestMapper::class)->do();
        }

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
                /** @var HttpMiddleware $middleware */
                $middleware = $this->container->get($middlewareClass->getName());

                return $middleware($request, $callable);
            });
        }

        return $callable;
    }

    public function toUri(array|string $action, ...$params): string
    {
        if (is_string($action) && str_starts_with($action, '/')) {
            $uri = $action;
        } else {
            [$controllerClass, $controllerMethod] = is_array($action) ? $action : [$action, '__invoke'];

            $routeAttribute = new ClassReflector($controllerClass)
                ->getMethod($controllerMethod)
                ->getAttribute(Route::class);

            if ($routeAttribute === null) {
                throw new ControllerMethodHadNoRouteAttribute($controllerClass, $controllerMethod);
            }

            $uri = $routeAttribute->uri;
        }

        $uri = str($uri);
        $queryParams = [];

        foreach ($params as $key => $value) {
            if (! $uri->matches(sprintf('/\{%s(\}|:)/', $key))) {
                $queryParams[$key] = $value;

                continue;
            }

            if ($value instanceof BackedEnum) {
                $value = $value->value;
            }

            $uri = $uri->replaceRegex(
                '#\{' . $key . DiscoveredRoute::ROUTE_PARAM_CUSTOM_REGEX . '\}#',
                (string) $value,
            );
        }

        $uri = $uri->prepend(rtrim($this->appConfig->baseUri, '/'));

        if ($queryParams !== []) {
            return $uri->append('?' . http_build_query($queryParams))->toString();
        }

        return $uri->toString();
    }

    public function isCurrentUri(array|string $action, ...$params): bool
    {
        $matchedRoute = $this->container->get(MatchedRoute::class);
        $candidateUri = $this->toUri($action, ...[...$matchedRoute->params, ...$params]);
        $currentUri = $this->toUri([$matchedRoute->route->handler->getDeclaringClass(), $matchedRoute->route->handler->getName()]);

        foreach ($matchedRoute->params as $key => $value) {
            if ($value instanceof BackedEnum) {
                $value = $value->value;
            }

            $currentUri = replace($currentUri, '/({' . preg_quote($key, '/') . '(?::.*?)?})/', $value);
        }

        return $currentUri === $candidateUri;
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
