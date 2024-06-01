<?php

declare(strict_types=1);

namespace Tempest\Http;

use ReflectionClass;
use Tempest\Container\Container;
use Tempest\Container\DynamicInitializer;
use Tempest\Database\Id;
use Tempest\Database\Model;

final class RouteBindingInitializer implements DynamicInitializer
{
    public function canInitialize(string $className): bool
    {
        return is_a($className, Model::class, true);
    }

    public function initialize(string $className, Container $container): object
    {
        $matchedRoute = $container->get(MatchedRoute::class);

        $paramName = lcfirst((new ReflectionClass($className))->getShortName());

        /** @var class-string<Model>|Model $className */
        return $className::find(new Id($matchedRoute->params[$paramName]));
    }
}
