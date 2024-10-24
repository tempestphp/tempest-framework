<?php

declare(strict_types=1);

namespace Tempest\Http\Routing\Matching;

use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Tempest\Http\MatchedRoute;

interface RouteMatcher
{
    public function match(PsrRequest $request): ?MatchedRoute;
}
