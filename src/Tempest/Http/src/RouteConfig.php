<?php

declare(strict_types=1);

namespace Tempest\Http;

use Tempest\Http\Routing\Matching\MatchingRegexes;

final class RouteConfig
{
    public function __construct(
        /** @var array<string, array<string, Route>> */
        public array $staticRoutes = [],
        /** @var array<string, array<string, Route>> */
        public array $dynamicRoutes = [],
        /** @var array<string, MatchingRegexes> */
        public array $matchingRegexes = [],
    ) {
    }

    public function apply(RouteConfig $newConfig): void
    {
        $this->staticRoutes = $newConfig->staticRoutes;
        $this->dynamicRoutes = $newConfig->dynamicRoutes;
        $this->matchingRegexes = $newConfig->matchingRegexes;
    }
}
