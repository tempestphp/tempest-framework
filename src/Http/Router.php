<?php

declare(strict_types=1);

namespace Tempest\Http;

interface Router
{
    public function dispatch(Request $request): Response;

    public function toUri(array|string $action, ...$params): string;

    public function matchRoute(Request $request): ?MatchedRoute;

    /**
     * @return \Tempest\Http\Route[][]
     */
    public function getRoutes(): array;
}
