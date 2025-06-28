<?php

declare(strict_types=1);

namespace Tempest\Router\Exceptions;

use Exception;
use Tempest\Router\Routing\Construction\DiscoveredRoute;

final class ControllerActionHadNoReturn extends Exception implements RouterException
{
    public function __construct(DiscoveredRoute $route)
    {
        parent::__construct(sprintf(
            "The controller action %s::%s doesn't return a valid response",
            $route->handler->getDeclaringClass()->getName(),
            $route->handler->getName(),
        ));
    }
}
