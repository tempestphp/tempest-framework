<?php

declare(strict_types=1);

namespace Tempest\Router;

final readonly class MatchedRoute
{
    public function __construct(
        public Route $route,
        public array $params,
    ) {
    }
}
