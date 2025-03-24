<?php

declare(strict_types=1);

namespace Tempest\Router;

use Tempest\Router\Routing\Construction\DiscoveredRoute;
use Tempest\Router\Routing\Matching\MatchingRegex;

final class RouteConfig
{
    public function __construct(
        /** @var array<string, array<string, DiscoveredRoute>> */
        public array $staticRoutes = [],
        /** @var array<string, array<string, DiscoveredRoute>> */
        public array $dynamicRoutes = [],
        /** @var array<string, MatchingRegex> */
        public array $matchingRegexes = [],
    ) {}

    public function apply(RouteConfig $newConfig): void
    {
        $this->staticRoutes = $newConfig->staticRoutes;
        $this->dynamicRoutes = $newConfig->dynamicRoutes;
        $this->matchingRegexes = $newConfig->matchingRegexes;
    }
}
