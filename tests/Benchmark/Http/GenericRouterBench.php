<?php

declare(strict_types=1);

namespace Tests\Tempest\Benchmark\Http;

use Generator;
use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\ParamProviders;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use ReflectionMethod;
use Tempest\Container\Container;
use Tempest\Container\GenericContainer;
use Tempest\Core\Middleware;
use Tempest\Http\GenericRequest;
use Tempest\Http\Method;
use Tempest\Http\Responses\Ok;
use Tempest\Reflection\MethodReflector;
use Tempest\Router\GenericRouter;
use Tempest\Router\Get;
use Tempest\Router\HandleRouteExceptionMiddleware;
use Tempest\Router\MatchRouteMiddleware;
use Tempest\Router\RouteConfig;
use Tempest\Router\Routing\Construction\DiscoveredRoute;
use Tempest\Router\Routing\Construction\RouteConfigurator;
use Tempest\Router\Routing\Matching\GenericRouteMatcher;
use Tempest\Router\Routing\Matching\RouteMatcher;

final class GenericRouterBench
{
    private GenericRouter $router;

    private GenericRouter $routerWithoutExceptionMiddleware;

    public function setUp(): void
    {
        $routeConfig = self::makeRouteConfig();

        $routeConfig->middleware = new Middleware(
            HandleRouteExceptionMiddleware::class,
            MatchRouteMiddleware::class,
        );

        $container = new GenericContainer();

        $matcher = new GenericRouteMatcher($routeConfig);

        $container->singleton(Container::class, fn () => $container);
        $container->singleton(RouteMatcher::class, fn () => $matcher);
        $container->singleton(RouteConfig::class, fn () => $routeConfig);

        $this->router = new GenericRouter($container, $routeConfig);

        $routeConfigWithoutExceptionMiddleware = self::makeRouteConfig();
        $routeConfigWithoutExceptionMiddleware->middleware = new Middleware(
            MatchRouteMiddleware::class,
        );

        $containerWithoutExceptionMiddleware = new GenericContainer();
        $matcherWithoutExceptionMiddleware = new GenericRouteMatcher($routeConfigWithoutExceptionMiddleware);

        $containerWithoutExceptionMiddleware->singleton(Container::class, fn () => $containerWithoutExceptionMiddleware);
        $containerWithoutExceptionMiddleware->singleton(RouteMatcher::class, fn () => $matcherWithoutExceptionMiddleware);
        $containerWithoutExceptionMiddleware->singleton(RouteConfig::class, fn () => $routeConfigWithoutExceptionMiddleware);

        $this->routerWithoutExceptionMiddleware = new GenericRouter($containerWithoutExceptionMiddleware, $routeConfigWithoutExceptionMiddleware);
    }

    #[BeforeMethods('setUp')]
    #[Iterations(5)]
    #[ParamProviders('provideDispatchCases')]
    #[Revs(1000)]
    #[Warmup(10)]
    public function benchDispatch(array $params): void
    {
        $this->router->dispatch(
            new GenericRequest(Method::GET, $params['uri']),
        );
    }

    #[BeforeMethods('setUp')]
    #[Iterations(5)]
    #[ParamProviders('provideDispatchCases')]
    #[Revs(1000)]
    #[Warmup(10)]
    public function benchDispatchWithoutMiddleware(array $params): void
    {
        $this->routerWithoutExceptionMiddleware->dispatch(
            new GenericRequest(Method::GET, $params['uri']),
        );
    }

    public function provideDispatchCases(): Generator
    {
        yield 'Static route' => ['uri' => '/test/5'];
        yield 'Dynamic route' => ['uri' => '/test/key/5/edit'];
        yield 'Dynamic short' => ['uri' => '/test/key/50'];
    }

    public function handle(): Ok
    {
        return new Ok('OK');
    }

    public function handleWithParam(string $id): Ok
    {
        return new Ok('OK');
    }

    private static function makeRouteConfig(): RouteConfig
    {
        $handler = new MethodReflector(new ReflectionMethod(self::class, 'handle'));
        $handlerWithParam = new MethodReflector(new ReflectionMethod(self::class, 'handleWithParam'));

        $configurator = new RouteConfigurator();

        foreach (range(1, 100) as $i) {
            $configurator->addRoute(DiscoveredRoute::fromRoute(
                new Get("/test/{$i}"),
                [],
                $handler,
            ));
            $configurator->addRoute(DiscoveredRoute::fromRoute(
                new Get("/test/{id}/{$i}"),
                [],
                $handlerWithParam,
            ));
            $configurator->addRoute(DiscoveredRoute::fromRoute(
                new Get("/test/{id}/{$i}/delete"),
                [],
                $handlerWithParam,
            ));
            $configurator->addRoute(DiscoveredRoute::fromRoute(
                new Get("/test/{id}/{$i}/edit"),
                [],
                $handlerWithParam,
            ));
        }

        return $configurator->toRouteConfig();
    }
}
