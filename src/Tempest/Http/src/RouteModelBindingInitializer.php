<?php

declare(strict_types=1);

namespace Tempest\Http;

use Tempest\Container\Container;
use Tempest\Container\DynamicInitializer;
use Tempest\Database\DatabaseModel;
use Tempest\Database\Id;
use Tempest\Http\Exceptions\NotFoundException;
use Tempest\Reflection\ClassReflector;

final class RouteModelBindingInitializer implements DynamicInitializer
{
    public function canInitialize(ClassReflector $class): bool
    {
        return $class->getType()->matches(DatabaseModel::class);
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

        $model = $class->callStatic('find', new Id($matchedRoute->params[$parameter->getName()]));

        if ($model === null) {
            throw new NotFoundException();
        }

        return $model;
    }
}
