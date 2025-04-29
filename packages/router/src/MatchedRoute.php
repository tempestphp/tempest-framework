<?php

declare(strict_types=1);

namespace Tempest\Router;

use Tempest\Router\Routing\Construction\DiscoveredRoute;

final readonly class MatchedRoute
{
    public function __construct(
        public DiscoveredRoute $route,
        public array $params,
    ) {}
}
