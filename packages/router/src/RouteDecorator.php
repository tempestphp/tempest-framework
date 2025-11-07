<?php

namespace Tempest\Router;

use Tempest\Router\Routing\Construction\DiscoveredRoute;

interface RouteDecorator
{
    public function decorate(DiscoveredRoute $route): DiscoveredRoute;
}