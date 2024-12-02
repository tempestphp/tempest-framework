<?php

declare(strict_types=1);

namespace Tests\Tempest\Benchmark\Http;

use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use Tempest\Http\Method;
use Tempest\Router\Route;
use Tempest\Router\RouteConfig;
use Tempest\Router\Routing\Construction\RouteConfigurator;
use Tempest\Router\Tests\FakeRouteBuilder;

final class RouteConfigBench
{
    private RouteConfig $config;

    public function __construct()
    {
        $this->config = self::makeRouteConfig();
    }

    #[Revs(1000)]
    #[Warmup(10)]
    public function benchSerialization(): void
    {
        $serialized = serialize($this->config);
        unserialize($serialized);
    }

    private static function makeRouteConfig(): RouteConfig
    {
        $routeBuilder = new FakeRouteBuilder();

        $configurator = new RouteConfigurator();
        foreach (range(1, 100) as $i) {
            $configurator->addRoute($routeBuilder->withUri("/test/{$i}")->asDiscoveredRoute());
            $configurator->addRoute($routeBuilder->withUri("/test/{id}/{$i}")->asDiscoveredRoute());
            $configurator->addRoute($routeBuilder->withUri("/test/{id}/{$i}/delete")->asDiscoveredRoute());
            $configurator->addRoute($routeBuilder->withUri("/test/{id}/{$i}/edit")->asDiscoveredRoute());
        }

        return $configurator->toRouteConfig();
    }
}
