<?php

namespace Tempest\Discovery;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use Tempest\Http\Route;
use Tempest\Http\RouteConfig;
use Tempest\Interfaces\Discoverer;

final readonly class ControllerDiscoverer implements Discoverer
{
    public function __construct(private RouteConfig $routeConfig)
    {
    }

    public function discover(ReflectionClass $class): void
    {
        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $attributes = $method->getAttributes(Route::class, ReflectionAttribute::IS_INSTANCEOF);

            if ($attributes !== []) {
                $this->routeConfig->addController($class->getName());

                return;
            }
        }
    }
}
