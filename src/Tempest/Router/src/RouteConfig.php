<?php

declare(strict_types=1);

namespace Tempest\Router;

use Tempest\Core\Middleware;
use Tempest\Router\Cookie\SetCookieMiddleware;

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

        /** @var Middleware<\Tempest\Router\HttpMiddleware> */
        public Middleware $middleware = new Middleware(
            SetCookieMiddleware::class,
        ),
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
