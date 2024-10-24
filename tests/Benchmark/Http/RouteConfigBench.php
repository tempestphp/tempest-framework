<?php

declare(strict_types=1);

namespace Tests\Tempest\Benchmark\Http;

use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use ReflectionMethod;
use Tempest\Http\Method;
use Tempest\Http\Response;
use Tempest\Http\Responses\Ok;
use Tempest\Http\Route;
use Tempest\Http\RouteConfig;
use Tempest\Reflection\MethodReflector;

final class RouteConfigBench
{
    private RouteConfig $config;

    public function __construct()
    {
        $this->config = new RouteConfig();
    }

    #[Warmup(10)]
    #[Revs(1000)]
    public function benchRoutingSetup(): void
    {
        $this->config = new RouteConfig();
        $this->setupRouter();
    }

    #[Warmup(10)]
    #[Revs(1000)]
    #[BeforeMethods("setupRouter")]
    public function benchSerialization(): void
    {
        $serialized = serialize($this->config);
        unserialize($serialized);
    }

    public function setupRouter(): void
    {
        $method = new MethodReflector(new ReflectionMethod(self::class, 'dummyMethod'));
        foreach (range(1, 100) as $i) {
            $this->config->addRoute($method, new Route("/test/{$i}", Method::GET));
            $this->config->addRoute($method, new Route("/test/{id}/{$i}", Method::GET));
            $this->config->addRoute($method, new Route("/test/{id}/{$i}/delete", Method::GET));
            $this->config->addRoute($method, new Route("/test/{id}/{$i}/edit", Method::GET));
        }
    }

    public static function dummyMethod(): Response
    {
        return new Ok();
    }
}