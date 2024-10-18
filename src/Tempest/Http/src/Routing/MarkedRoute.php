<?php

declare(strict_types=1);

namespace Tempest\Http\Routing;

use Tempest\Http\Route;

final readonly class MarkedRoute
{
    public function __construct(
        public string $mark,
        public Route $route,
    ) {}
}