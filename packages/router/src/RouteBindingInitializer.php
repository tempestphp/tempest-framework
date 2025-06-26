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
    public function canInitialize(ClassReflector $class, null|string|UnitEnum $tag): bool
    {
        return $class->getType()->matches(Bindable::class);
    }

    public function initialize(ClassReflector $class, null|string|UnitEnum $tag, Container $container): object
    {
        $matchedRoute = $container->get(MatchedRoute::class);

        $parameter = null;

        foreach ($matchedRoute->route->handler->getParameters() as $searchParameter) {
            if ($searchParameter->getType()->equals($class->getType())) {
                $parameter = $searchParameter;

                break;
            }
        }

        $object = $class->callStatic('resolve', $matchedRoute->params[$parameter->getName()]);

        if ($object === null) {
            throw new RouteBindingFailed();
        }

        return $object;
    }
}
