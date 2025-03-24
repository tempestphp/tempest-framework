<?php

declare(strict_types=1);

namespace Tests\Tempest\Benchmark\Http\Routing\Matching;

use Generator;
use Laminas\Diactoros\ServerRequest;
use PhpBench\Attributes\ParamProviders;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use Tempest\Router\RouteConfig;
use Tempest\Router\Routing\Construction\RouteConfigurator;
use Tempest\Router\Routing\Matching\GenericRouteMatcher;
use Tempest\Router\Tests\FakeRouteBuilder;

final class GenericRouteMatcherBench
{
    private GenericRouteMatcher $matcher;

    public function __construct()
    {
        $config = self::makeRouteConfig();

        $this->matcher = new GenericRouteMatcher($config);
    }

    #[ParamProviders('provideDynamicMatchingCases')]
    #[Revs(1000)]
    #[Warmup(10)]
    public function benchMatch(array $params): void
    {
        $this->matcher->match(
            new ServerRequest(uri: $params['uri'], method: 'GET'),
        );
    }

    public function provideDynamicMatchingCases(): Generator
    {
        yield 'Dynamic' => ['uri' => '/test/key/5/edit'];
        yield 'Non existing long' => ['uri' => '/test/key/5/nonexisting'];
        yield 'Non existing short' => ['uri' => '/404'];
        yield 'Static route' => ['uri' => '/test/5'];
    }

    private static function makeRouteConfig(): RouteConfig
    {
        $routeBuilder = new FakeRouteBuilder();
        $constructor = new RouteConfigurator();
        foreach (range(1, 100) as $i) {
            $constructor->addRoute($routeBuilder->withUri("/test/{$i}")->asDiscoveredRoute());
            $constructor->addRoute($routeBuilder->withUri("/test/{id}/{$i}")->asDiscoveredRoute());
            $constructor->addRoute($routeBuilder->withUri("/test/{id}/{$i}/delete")->asDiscoveredRoute());
            $constructor->addRoute($routeBuilder->withUri("/test/{id}/{$i}/edit")->asDiscoveredRoute());
        }

        return $constructor->toRouteConfig();
    }
}
