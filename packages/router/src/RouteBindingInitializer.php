<?php

declare(strict_types=1);

namespace Tempest\Router;

use Tempest\Container\Container;
use Tempest\Container\DynamicInitializer;
use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\ParameterReflector;
use Tempest\Router\Exceptions\RouteBindingDidNotSupportRelations;
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

        $withRelations = $parameter->getAttribute(WithRelations::class);

        if ($withRelations !== null) {
            $resolve = $class->getMethod('resolve');

            if (! $resolve->getParameter('relations') instanceof ParameterReflector && ! $resolve->getReflection()->isVariadic()) {
                throw new RouteBindingDidNotSupportRelations($class->getName());
            }
        }

        $input = $matchedRoute->params[$parameter->getName()];

        $object = match ($withRelations) {
            null => $class->callStatic('resolve', $input),
            default => $class->callStatic('resolve', $input, relations: $withRelations->relations),
        };

        if ($object === null) {
            throw new RouteBindingFailed();
        }

        return $object;
    }
}
