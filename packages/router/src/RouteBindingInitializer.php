<?php

declare(strict_types=1);

namespace Tempest\Router;

use Tempest\Container\Container;
use Tempest\Container\DynamicInitializer;
use Tempest\Container\Tag;
use Tempest\Reflection\ClassReflector;
use Tempest\Router\Exceptions\NotFoundException;

final class RouteBindingInitializer implements DynamicInitializer
{
    public function canInitialize(ClassReflector $class, ?string $tag): bool
    {
        return $class->getType()->matches(Bindable::class);
    }

    public function initialize(ClassReflector $class, ?string $tag, Container $container): object
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
            throw new NotFoundException();
        }

        return $object;
    }
}
