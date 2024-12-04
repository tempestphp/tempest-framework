<?php

declare(strict_types=1);

namespace Tempest\Http;

use BackedEnum;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use ReflectionException;
use Tempest\Container\Container;
use Tempest\Core\AppConfig;
use Tempest\Http\Exceptions\ControllerActionHasNoReturn;
use Tempest\Http\Exceptions\InvalidRouteException;
use Tempest\Http\Exceptions\NotFoundException;
use Tempest\Http\Mappers\RequestToPsrRequestMapper;
use Tempest\Http\Responses\Invalid;
use Tempest\Http\Responses\NotFound;
use Tempest\Http\Responses\Ok;
use Tempest\Http\Routing\Matching\RouteMatcher;
use Tempest\Reflection\ClassReflector;
use Tempest\Validation\Exceptions\ValidationException;
use Tempest\View\View;
use function Tempest\map;
use function Tempest\Support\str;

/**
 * @template MiddlewareClass of \Tempest\Http\HttpMiddleware
 */
final class GenericRouter implements Router
{
    /** @var class-string<MiddlewareClass>[] */
    private array $middleware = [];

    public function __construct(
        private readonly Container $container,
        private readonly RouteMatcher $routeMatcher,
        private readonly AppConfig $appConfig,
    ) {
    }

    public function dispatch(Request|PsrRequest $request): Response
    {
        if (! $request instanceof PsrRequest) {
            $request = map($request)->with(RequestToPsrRequestMapper::class);
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

        try {
            $request = $this->resolveRequest($request, $matchedRoute);
            $response = $callable($request);
        } catch (NotFoundException) {
            return new NotFound();
        } catch (ValidationException $validationException) {
            return new Invalid($request, $validationException->failingRules);
        }

        return $response;
    }

    private function getCallable(MatchedRoute $matchedRoute): HttpMiddlewareCallable
    {
        $route = $matchedRoute->route;

        $callControllerAction = function (Request $request) use ($route, $matchedRoute) {
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
                '#\{' . $key . Route::ROUTE_PARAM_CUSTOM_REGEX . '\}#',
                (string) $value,
            );
        }

        $uri = $uri->prepend(rtrim($this->appConfig->baseUri, '/'));

        if ($queryParams !== []) {
            return $uri->append('?' . http_build_query($queryParams))->toString();
        }

        return $uri->toString();
    }

    private function createResponse(Response|View $input): Response
    {
        if ($input instanceof View) {
            return new Ok($input);
        }

        return $input;
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
        /** @var Request $request */
        $request = map($psrRequest)->to($requestClass);

        // Next, we register this newly created request object in the container
        // This makes it so that RequestInitializer is bypassed entirely when the controller action needs the request class
        // Making it so that we don't need to set any $_SERVER variables and stuff like that
        $this->container->singleton(Request::class, fn () => $request);
        $this->container->singleton($request::class, fn () => $request);

        // Finally, we validate the request
        $request->validate();

        return $request;
    }

    public function addMiddleware(string $middlewareClass): void
    {
        $this->middleware[] = $middlewareClass;
    }
}
