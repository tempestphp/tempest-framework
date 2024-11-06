<?php

declare(strict_types=1);

namespace Tests\Tempest\Benchmark\Http;

use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use Tempest\Http\Method;
use Tempest\Http\Route;
use Tempest\Http\RouteConfig;
use Tempest\Http\Routing\Construction\RouteConfigurator;

final class RouteConfigBench
{
    private RouteConfig $config;

    public function __construct()
    {
        $this->config = self::makeRouteConfig();
    }

    #[Warmup(10)]
    #[Revs(1000)]
    public function benchSerialization(): void
    {
        $serialized = serialize($this->config);
        unserialize($serialized);
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
