<?php

declare(strict_types=1);

namespace Tests\Tempest\Benchmark\Http\Routing\Matching;

use Generator;
use Laminas\Diactoros\ServerRequest;
use PhpBench\Attributes\ParamProviders;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use Tempest\Http\Method;
use Tempest\Http\Route;
use Tempest\Http\RouteConfig;
use Tempest\Http\Routing\Construction\RouteConfigurator;
use Tempest\Http\Routing\Matching\GenericRouteMatcher;

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
        yield 'Dynamic' => [ 'uri' => '/test/key/5/edit' ];
        yield 'Non existing long' => [ 'uri' => '/test/key/5/nonexisting' ];
        yield 'Non existing short' => [ 'uri' => '/404' ];
        yield 'Static route' => [ 'uri' => '/test/5' ];
    }

    private static function makeRouteConfig(): RouteConfig
    {
        $constructor = new RouteConfigurator();
        foreach (range(1, 100) as $i) {
            $constructor->addRoute(new Route("/test/{$i}", Method::GET));
            $constructor->addRoute(new Route("/test/{id}/{$i}", Method::GET));
            $constructor->addRoute(new Route("/test/{id}/{$i}/delete", Method::GET));
            $constructor->addRoute(new Route("/test/{id}/{$i}/edit", Method::GET));
        }

        return $constructor->toRouteConfig();
    }
}
