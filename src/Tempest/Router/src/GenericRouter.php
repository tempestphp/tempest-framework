<?php

declare(strict_types=1);

namespace Tempest\Router;

use BackedEnum;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use ReflectionException;
use Tempest\Container\Container;
use Tempest\Core\AppConfig;
use Tempest\Reflection\ClassReflector;
use Tempest\Router\Exceptions\ControllerActionHasNoReturn;
use Tempest\Router\Exceptions\InvalidRouteException;
use Tempest\Router\Exceptions\NotFoundException;
use Tempest\Router\Mappers\PsrRequestToGenericRequestMapper;
use Tempest\Router\Mappers\RequestToObjectMapper;
use Tempest\Router\Mappers\RequestToPsrRequestMapper;
use Tempest\Router\Responses\Invalid;
use Tempest\Router\Responses\NotFound;
use Tempest\Router\Responses\Ok;
use Tempest\Router\Routing\Construction\DiscoveredRoute;
use Tempest\Router\Routing\Matching\RouteMatcher;
use Tempest\Validation\Exceptions\ValidationException;
use Tempest\View\View;

use function Tempest\map;
use function Tempest\Support\Regex\replace;
use function Tempest\Support\str;

/**
 * @template MiddlewareClass of \Tempest\Router\HttpMiddleware
 */
final class GenericRouter implements Router
{
    /** @var class-string<MiddlewareClass>[] */
    private array $middleware = [];

    private bool $handleExceptions = true;

    public function __construct(
        private readonly Container $container,
        private readonly RouteMatcher $routeMatcher,
        private readonly AppConfig $appConfig,
    ) {
    }

    public function throwExceptions(): self
    {
        $this->handleExceptions = false;

        return $this;
    }

    public function dispatch(Request|PsrRequest $request): Response
    {
        if (! ($request instanceof PsrRequest)) {
            $request = map($request)->with(RequestToPsrRequestMapper::class)->do();
        }

        $matchedRoute = $this->routeMatcher->match($request);

        if ($matchedRoute === null) {
            return new NotFound();
        }

        $this->container->singleton(
            MatchedRoute::class,
            fn () => $matchedRoute,
        );

        $callable = $this->getCallable($matchedRoute);

        if ($this->handleExceptions) {
            try {
                $request = $this->resolveRequest($request, $matchedRoute);
                $response = $callable($request);
            } catch (NotFoundException) {
                return new NotFound();
            } catch (ValidationException $validationException) {
                return new Invalid($validationException->object, $validationException->failingRules);
            }
        } else {
            $request = $this->resolveRequest($request, $matchedRoute);
            $response = $callable($request);
        }

        return $response;
    }

    private function getCallable(MatchedRoute $matchedRoute): HttpMiddlewareCallable
    {
        $route = $matchedRoute->route;

        $callControllerAction = function (Request $_) use ($route, $matchedRoute) {
            $response = $this->container->invoke(
                $route->handler,
                ...$matchedRoute->params,
            );

            if ($response === null) {
                throw new ControllerActionHasNoReturn($route);
            }

            return $response;
        };

        $callable = new HttpMiddlewareCallable(fn (Request $request) => $this->createResponse($callControllerAction($request)));

        $middlewareStack = [...$this->middleware, ...$route->middleware];

        while ($middlewareClass = array_pop($middlewareStack)) {
            $callable = new HttpMiddlewareCallable(function (Request $request) use ($middlewareClass, $callable) {
                /** @var HttpMiddleware $middleware */
                $middleware = $this->container->get($middlewareClass);

                return $middleware($request, $callable);
            });
        }

        return $callable;
    }

    public function toUri(array|string $action, ...$params): string
    {
        try {
            if (is_array($action)) {
                $controllerClass = $action[0];
                $reflection = new ClassReflector($controllerClass);
                $controllerMethod = $reflection->getMethod($action[1]);
            } else {
                $controllerClass = $action;
                $reflection = new ClassReflector($controllerClass);
                $controllerMethod = $reflection->getMethod('__invoke');
            }

            /** @var Route|null $routeAttribute */
            $routeAttribute = $controllerMethod->getAttribute(Route::class);

            $uri = $routeAttribute->uri;
        } catch (ReflectionException) {
            if (is_array($action)) {
                throw new InvalidRouteException($action[0], $action[1]);
            }

            $uri = $action;
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
            $currentUri = replace($currentUri, '/({' . preg_quote($key, '/') . '(?::.*?)?})/', $value);
        }

        return $currentUri === $candidateUri;
    }

    private function createResponse(Response|View $input): Response
    {
        if ($input instanceof View) {
            return new Ok($input);
        }

        return $input;
    }

    // TODO: could in theory be moved to a dynamic initializer
    private function resolveRequest(\Psr\Http\Message\ServerRequestInterface|\Tempest\Mapper\ObjectFactory $psrRequest, MatchedRoute $matchedRoute): Request
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
        /** @var \Tempest\Router\GenericRequest $request */
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

    public function addMiddleware(string $middlewareClass): void
    {
        $this->middleware[] = $middlewareClass;
    }
}
