<?php

declare(strict_types=1);

namespace Tempest\Router\Routing\Matching;

use Tempest\Http\Request;
use Tempest\Router\MatchedRoute;

interface RouteMatcher
{
    public function match(Request $request): ?MatchedRoute;
}
