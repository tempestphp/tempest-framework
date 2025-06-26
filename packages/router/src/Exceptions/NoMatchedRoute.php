<?php

namespace Tempest\Router\Exceptions;

use Exception;

final class NoMatchedRoute extends Exception
{
    public function __construct()
    {
        parent::__construct('No matched route was registered in the container. Did you remove `\Tempest\Router\MatchRouteMiddleware` from the middleware stack by any chance?');
    }
}