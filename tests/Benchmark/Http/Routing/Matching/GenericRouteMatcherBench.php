<?php

declare(strict_types=1);

namespace Tests\Tempest\Benchmark\Http\Routing\Matching;

use Generator;
use Laminas\Diactoros\ServerRequest;
use PhpBench\Attributes\ParamProviders;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use ReflectionMethod;
use Tempest\Http\Method;
use Tempest\Http\Response;
use Tempest\Http\Responses\Ok;
use Tempest\Http\Route;
use Tempest\Http\RouteConfig;
use Tempest\Http\Routing\Matching\GenericRouteMatcher;
use Tempest\Reflection\MethodReflector;

final class GenericRouteMatcherBench
{
    private RouteConfig $config;

    private GenericRouteMatcher $matcher;

    public function __construct()
    {
        $this->config = new RouteConfig();

        $this->matcher = new GenericRouteMatcher($this->config);

        $this->setupConfigRoutes();
    }

    #[Warmup(10)]
    #[Revs(1000)]
    #[ParamProviders('provideDynamicMatchingCases')]
    public function benchMatch(array $params): void
    {
        $this->matcher->match(
            new ServerRequest(uri: $params['uri'], method: 'GET')
        );
    }

    public function setupConfigRoutes(): void
    {
        $method = new MethodReflector(new ReflectionMethod(self::class, 'dummyMethod'));
        foreach (range(1, 100) as $i) {
            $this->config->addRoute($method, new Route("/test/{$i}", Method::GET));
            $this->config->addRoute($method, new Route("/test/{id}/{$i}", Method::GET));
            $this->config->addRoute($method, new Route("/test/{id}/{$i}/delete", Method::GET));
            $this->config->addRoute($method, new Route("/test/{id}/{$i}/edit", Method::GET));
        }
    }

    public function provideDynamicMatchingCases(): Generator
    {
        yield 'Dynamic' => [ 'uri' => '/test/key/5/edit' ];
        yield 'Non existing long' => [ 'uri' => '/test/key/5/nonexisting' ];
        yield 'Non existing short' => [ 'uri' => '/404' ];
        yield 'Static route' => [ 'uri' => '/test/5' ];
    }

    public static function dummyMethod(): Response
    {
        return new Ok();
    }
}
