<?php

declare(strict_types=1);

namespace Tempest\Router;

use Tempest\Reflection\ClassReflector;

final class RouteConfig
{
    public function __construct(
        /** @var array<string,array<string,\Tempest\Router\Routing\Construction\DiscoveredRoute>> */
        public array $staticRoutes = [],
        /** @var array<string,array<string,\Tempest\Router\Routing\Construction\DiscoveredRoute>> */
        public array $dynamicRoutes = [],
        /** @var array<string,\Tempest\Router\Routing\Matching\MatchingRegex> */
        public array $matchingRegexes = [],
        /** @var class-string<\Tempest\Router\ResponseProcessor>[] */
        public array $responseProcessors = [],
    ) {}

    public function apply(RouteConfig $newConfig): void
    {
        $this->staticRoutes = $newConfig->staticRoutes;
        $this->dynamicRoutes = $newConfig->dynamicRoutes;
        $this->matchingRegexes = $newConfig->matchingRegexes;
    }

    public function addResponseProcessor(string $responseProcessor): void
    {
        $this->responseProcessors[] = $responseProcessor;
    }
}
