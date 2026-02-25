<?php

declare(strict_types=1);

namespace Tests\Tempest\Benchmark\Http\Routing\Construction;

use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use Tempest\Router\Routing\Construction\RouteConfigurator;
use Tempest\Router\Tests\FakeRouteBuilder;

final class RouteConfiguratorBench
{
    private RouteConfigurator $subject;

    #[BeforeMethods('setUpConfiguredRoutes')]
    #[Iterations(5)]
    #[Revs(1000)]
    #[Warmup(10)]
    public function benchBuildRouteConfig(): void
    {
        $this->subject->toRouteConfig();
    }

    #[Iterations(5)]
    #[Revs(1000)]
    #[Warmup(10)]
    public function benchRegisterRoutes(): void
    {
        $configurator = new RouteConfigurator();

        $this->addRoutes($configurator);
    }

    public function setUpConfiguredRoutes(): void
    {
        $this->subject = new RouteConfigurator();
        $this->addRoutes($this->subject);
    }

    private function addRoutes(RouteConfigurator $configurator): void
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
