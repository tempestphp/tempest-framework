<?php

declare(strict_types=1);

namespace Tempest\Http;

use BackedEnum;
use Tempest\Container\Container;
use Tempest\Container\DynamicInitializer;
use Tempest\Http\Exceptions\NotFoundException;
use Tempest\Reflection\ClassReflector;

final class RouteEnumBindingInitializer implements DynamicInitializer
{
    public function canInitialize(ClassReflector $class): bool
    {
        return $class->getType()->matches(BackedEnum::class);
    }

    public function initialize(ClassReflector $class, Container $container): object
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
