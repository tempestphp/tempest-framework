<?php

declare(strict_types=1);

namespace Tempest\Http\Routing\Matching;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

final readonly class RouteMatcherInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): RouteMatcher
    {
        return $container->get(GenericRouteMatcher::class);
    }
}
