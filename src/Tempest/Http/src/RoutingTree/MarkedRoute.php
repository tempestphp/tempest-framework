<?php

declare(strict_types=1);

namespace Tempest\Http\RoutingTree;

use Tempest\Http\Route;

final readonly class MarkedRoute
{
    public function __construct(
        public string $mark,
        public Route $route,
    ) {}
}