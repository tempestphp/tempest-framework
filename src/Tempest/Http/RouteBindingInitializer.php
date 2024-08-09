<?php

declare(strict_types=1);

namespace Tempest\Http;

use Tempest\Container\Container;
use Tempest\Container\DynamicInitializer;
use Tempest\Database\Id;
use Tempest\Database\Model;
use Tempest\Support\Reflection\ClassReflector;

final class RouteBindingInitializer implements DynamicInitializer
{
    public function canInitialize(ClassReflector $class): bool
    {
        return $class->getType()->matches(Model::class);
    }

    public function initialize(ClassReflector $class, Container $container): object
    {
        $matchedRoute = $container->get(MatchedRoute::class);

        $paramName = lcfirst($class->getShortName());

        return $class->callStatic('find', new Id($matchedRoute->params[$paramName]));
    }
}
