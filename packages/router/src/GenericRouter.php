<?php

declare(strict_types=1);

namespace Tempest\Router;

use BackedEnum;
use Exception;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Tempest\Container\Container;
use Tempest\Core\AppConfig;
use Tempest\Http\GenericRequest;
use Tempest\Http\Mappers\PsrRequestToGenericRequestMapper;
use Tempest\Http\Mappers\RequestToObjectMapper;
use Tempest\Http\Mappers\RequestToPsrRequestMapper;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Invalid;
use Tempest\Http\Responses\NotFound;
use Tempest\Http\Responses\Ok;
use Tempest\Http\Responses\ServerError;
use Tempest\Mapper\ObjectFactory;
use Tempest\Reflection\ClassReflector;
use Tempest\Router\Exceptions\ControllerActionHasNoReturn;
use Tempest\Router\Exceptions\InvalidRouteException;
use Tempest\Router\Exceptions\NotFoundException;
use Tempest\Router\Routing\Construction\DiscoveredRoute;
use Tempest\Router\Routing\Matching\RouteMatcher;
use Tempest\Validation\Exceptions\ValidationException;
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
        return $this->processResponse(
            $this->processRequest($request),
        );
    }

    private function processRequest(Request|PsrRequest $request): Response
    {
        if (! ($request instanceof PsrRequest)) {
            $request = map($request)->with(RequestToPsrRequestMapper::class)->do();
        }

        $matchedRoute = $this->routeMatcher->match($request);

        if ($matchedRoute === null) {
            return new NotFound();
        }

        $this->container->singleton(MatchedRoute::class, fn () => $matchedRoute);

        try {
            $callable = $this->getCallable($matchedRoute);
            $request = $this->resolveRequest($request, $matchedRoute);
            $response = $callable($request);
        } catch (NotFoundException) {
            return new NotFound();
        } catch (ValidationException $validationException) {
            return new Invalid($validationException->subject, $validationException->failingRules);
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

        $middlewareStack = $this->routeConfig
            ->middleware
            ->clone()
            ->add(...$route->middleware);

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
                throw new InvalidRouteException($controllerClass, $controllerMethod);
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

    // TODO: could in theory be moved to a dynamic initializer
    private function resolveRequest(PsrRequest|ObjectFactory $psrRequest, MatchedRoute $matchedRoute): Request
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
