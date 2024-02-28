<?php

declare(strict_types=1);

namespace Tempest\Http;

use ReflectionClass;
use Tempest\Container\CanInitialize;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\RequiresClassName;
use Tempest\Database\Id;
use Tempest\ORM\Model;

final class RouteBindingInitializer implements Initializer, CanInitialize, RequiresClassName
{
    private string $className;

    public function canInitialize(string $className): bool
    {
        return is_a($className, Model::class, true);
    }

    public function setClassName(string $className): void
    {
        $this->className = $className;
    }

    public function initialize(Container $container): object
    {
        $matchedRoute = $container->get(MatchedRoute::class);

        $className = $this->className;

        $paramName = lcfirst((new ReflectionClass($className))->getShortName());

        /** @var class-string<Model>|Model $className */
        return $className::find(new Id($matchedRoute->params[$paramName]));
    }
}
