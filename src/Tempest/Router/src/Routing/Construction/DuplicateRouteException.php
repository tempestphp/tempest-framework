<?php

declare(strict_types=1);

namespace Tempest\Router\Routing\Construction;

use InvalidArgumentException;
use Tempest\Router\Route;

final class DuplicateRouteException extends InvalidArgumentException
{
    public function __construct(Route $route)
    {
        parent::__construct("Route '{$route->uri()}' already exists.");
    }
}
