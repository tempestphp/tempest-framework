<?php

declare(strict_types=1);

namespace Tempest\Router\Routing\Matching;

use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Tempest\Router\MatchedRoute;

interface RouteMatcher
{
    public function match(PsrRequest $request): ?MatchedRoute;
}
