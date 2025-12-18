<?php

declare(strict_types=1);

namespace Tempest\Router;

use Tempest\Core\Middleware;

final class RouteConfig
{
    public function __construct(
        /** @var array<string,array<string,\Tempest\Router\Routing\Construction\DiscoveredRoute>> */
        public array $staticRoutes = [],

        /** @var array<string,array<string,\Tempest\Router\Routing\Construction\DiscoveredRoute>> */
        public array $dynamicRoutes = [],

        /** @var array<string,\Tempest\Router\Routing\Matching\MatchingRegex> */
        public array $matchingRegexes = [],

        /** @var array<string,string[]> */
        public array $handlerIndex = [],

        /** @var class-string<\Tempest\Router\ResponseProcessor>[] */
        public array $responseProcessors = [],

        /** @var array<int,array<class-string<\Tempest\Router\Exceptions\ExceptionRenderer>>> */
        public array $exceptionRenderers = [],

        /** @var Middleware<\Tempest\Router\HttpMiddleware> */
        public Middleware $middleware = new Middleware(
            HandleRouteExceptionMiddleware::class,
            MatchRouteMiddleware::class,
            SetCookieMiddleware::class,
            HandleRouteSpecificMiddleware::class,
        ),
    ) {}

    public function apply(RouteConfig $newConfig): void
    {
        $this->staticRoutes = $newConfig->staticRoutes;
        $this->dynamicRoutes = $newConfig->dynamicRoutes;
        $this->matchingRegexes = $newConfig->matchingRegexes;
        $this->handlerIndex = $newConfig->handlerIndex;
    }

    public function addResponseProcessor(string $responseProcessor): void
    {
        $this->responseProcessors[] = $responseProcessor;
    }

    public function addExceptionRenderer(string $exceptionRenderer, int $priority): void
    {
        $this->exceptionRenderers[$priority] ??= [];
        $this->exceptionRenderers[$priority][] = $exceptionRenderer;
    }
}
