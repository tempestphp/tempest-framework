<?php

declare(strict_types=1);

namespace Tempest\Router;

use Tempest\Container\Container;
use Tempest\Container\DynamicInitializer;
use Tempest\Reflection\ClassReflector;
use Tempest\Router\Exceptions\RouteBindingFailed;
use UnitEnum;

final class RouteBindingInitializer implements DynamicInitializer
{
    public function canInitialize(ClassReflector $class, string|UnitEnum|null $tag): bool
    {
        return $class->getType()->matches(Bindable::class);
    }

    public function initialize(ClassReflector $class, string|UnitEnum|null $tag, Container $container): object
    {
        $matchedRoute = $container->get(MatchedRoute::class);

        $parameter = null;

        foreach ($matchedRoute->route->handler->getParameters() as $searchParameter) {
            if (! $searchParameter->getType()->equals($class->getType())) {
                continue;
            }

            $parameter = $searchParameter;

            break;
        }

        if ($parameter === null) {
            throw new RouteBindingFailed();
        }

        $object = $class->callStatic('resolve', $matchedRoute->params[$parameter->getName()]);

        if ($object === null) {
            throw new RouteBindingFailed();
        }

        return $object;
    }
}
