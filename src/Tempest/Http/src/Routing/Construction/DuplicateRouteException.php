<?php

declare(strict_types=1);

namespace Tempest\Http\Routing\Construction;

use InvalidArgumentException;
use Tempest\Http\Route;

final class DuplicateRouteException extends InvalidArgumentException
{
    public function __construct(Route $route)
    {
        parent::__construct("Route '{$route->uri}' already exists.");
    }
}
