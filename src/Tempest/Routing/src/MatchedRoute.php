<?php

declare(strict_types=1);

namespace Tempest\Routing;

final readonly class MatchedRoute
{
    public function __construct(
        public Route $route,
        public array $params,
    ) {
    }
}
