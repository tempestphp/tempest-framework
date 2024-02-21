<?php

declare(strict_types=1);

namespace Tempest\Http;

use ReflectionClass;
use Tempest\Container\CanInitialize;
use Tempest\Container\Container;
use Tempest\Database\Id;
use Tempest\ORM\Model;

final readonly class RouteBindingInitializer implements CanInitialize
{
    public function canInitialize(string $className): bool
    {
        return is_a($className, Model::class, true);
    }

    public function initialize(string $className, Container $container): object
    {
        $routeParams = $container->get(RouteParams::class);

        $paramName = lcfirst((new ReflectionClass($className))->getShortName());

        /** @var class-string<Model>|Model $className */
        return $className::find(new Id($routeParams->params[$paramName]));
    }
}
