<?php

declare(strict_types=1);

namespace Tempest\Http\Routing\Matching;

use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Http\Cookie\SetCookieMiddleware;
use Tempest\Http\GenericRouter;
use Tempest\Http\MatchedRoute;
use Tempest\Http\Router;

final class RouteMatcherInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): RouteMatcher
    {
        return $container->get(GenericRouteMatcher::class);
    }
}