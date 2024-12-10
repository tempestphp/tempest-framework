<?php

declare(strict_types=1);

namespace Tests\Tempest\Benchmark\Http\Routing\Construction;

use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use Tempest\Router\Routing\Construction\RouteConfigurator;
use Tempest\Router\Tests\FakeRouteBuilder;

final class RouteConfiguratorBench
{
    private RouteConfigurator $subject;

    public function __construct()
    {
        $this->subject = new RouteConfigurator();
    }

    #[BeforeMethods('setupRouteConfig')]
    #[Revs(1000)]
    #[Warmup(10)]
    public function benchRouteConfigConstructionToConfig(): void
    {
        $this->subject->toRouteConfig();
    }

    #[Revs(1000)]
    #[Warmup(10)]
    public function benchRouteConfigConstructionRouteAdding(): void
    {
        $configurator = new RouteConfigurator();
        $routeBuilder = new FakeRouteBuilder();

        foreach (range(1, 100) as $i) {
            $configurator->addRoute($routeBuilder->withUri("/test/{$i}")->asDiscoveredRoute());
            $configurator->addRoute($routeBuilder->withUri("/test/{id}/{$i}")->asDiscoveredRoute());
            $configurator->addRoute($routeBuilder->withUri("/test/{id}/{$i}/delete")->asDiscoveredRoute());
            $configurator->addRoute($routeBuilder->withUri("/test/{id}/{$i}/edit")->asDiscoveredRoute());
        }
    }

    public function setupRouteConfig(): void
    {
        self::addRoutes($this->subject);
    }

    private static function addRoutes(RouteConfigurator $configurator): void
    {
        $routeBuilder = new FakeRouteBuilder();

        foreach (range(1, 100) as $i) {
            $configurator->addRoute($routeBuilder->withUri("/test/{$i}")->asDiscoveredRoute());
            $configurator->addRoute($routeBuilder->withUri("/test/{id}/{$i}")->asDiscoveredRoute());
            $configurator->addRoute($routeBuilder->withUri("/test/{id}/{$i}/delete")->asDiscoveredRoute());
            $configurator->addRoute($routeBuilder->withUri("/test/{id}/{$i}/edit")->asDiscoveredRoute());
        }
    }
}
