<?php

namespace Tempest\Http\Static;

use Tempest\Http\StaticRoute;
use Tempest\Support\Reflection\MethodReflector;

final class StaticRouteConfig
{
    public function __construct(
        /** @var StaticRoute[] $staticRoutes */
        public array $staticRoutes = [],
    ) {}

    public function addHandler(StaticRoute $staticRoute, MethodReflector $methodReflector): void
    {
        $staticRoute->setHandler($methodReflector);

        $this->staticRoutes[] = $staticRoute;
    }
}