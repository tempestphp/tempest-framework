<?php

declare(strict_types=1);

namespace Tests\Tempest\Benchmark\Http\Routing\Construction;

use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use Tempest\Http\Method;
use Tempest\Http\Route;
use Tempest\Http\Routing\Construction\RouteConfigurator;

final class RouteConfiguratorBench
{
    private RouteConfigurator $subject;

    public function __construct()
    {
        $this->subject = new RouteConfigurator();
    }

    #[Warmup(10)]
    #[Revs(1000)]
    #[BeforeMethods("setupRouteConfig")]
    public function benchRouteConfigConstructionToConfig(): void
    {
        $this->subject->toRouteConfig();
    }

    #[Warmup(10)]
    #[Revs(1000)]
    public function benchRouteConfigConstructionRouteAdding(): void
    {
        $configurator = new RouteConfigurator();

        foreach (range(1, 100) as $i) {
            $configurator->addRoute(new Route("/test/{$i}", Method::GET));
            $configurator->addRoute(new Route("/test/{id}/{$i}", Method::GET));
            $configurator->addRoute(new Route("/test/{id}/{$i}/delete", Method::GET));
            $configurator->addRoute(new Route("/test/{id}/{$i}/edit", Method::GET));
        }
    }

    public function setupRouteConfig(): void
    {
        self::addRoutes($this->subject);
    }

    private static function addRoutes(RouteConfigurator $constructor): void
    {
        foreach (range(1, 100) as $i) {
            $constructor->addRoute(new Route("/test/{$i}", Method::GET));
            $constructor->addRoute(new Route("/test/{id}/{$i}", Method::GET));
            $constructor->addRoute(new Route("/test/{id}/{$i}/delete", Method::GET));
            $constructor->addRoute(new Route("/test/{id}/{$i}/edit", Method::GET));
        }
    }
}
