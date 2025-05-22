<?php

declare(strict_types=1);

namespace Tempest\Router;

use BackedEnum;
use Tempest\Container\Container;
use Tempest\Container\DynamicInitializer;
use Tempest\Reflection\ClassReflector;
use Tempest\Router\Exceptions\NotFoundException;
use UnitEnum;

final class RouteEnumBindingInitializer implements DynamicInitializer
{
    public function canInitialize(ClassReflector $class, null|string|UnitEnum $tag): bool
    {
        // TODO: should not be a global initializer
        return false;
        return $class->getType()->matches(BackedEnum::class);
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

        $enum = $class->callStatic('tryFrom', $matchedRoute->params[$parameter->getName()]);

        if ($enum === null) {
            throw new NotFoundException();
        }

        return $enum;
    }
}
