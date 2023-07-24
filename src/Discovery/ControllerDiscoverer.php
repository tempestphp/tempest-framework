<?php

declare(strict_types=1);

namespace Tempest\Discovery;

use ReflectionClass;
use ReflectionMethod;

use function Tempest\attribute;

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
            $attributes = attribute(Route::class)->in($method)->all();

            if ($attributes !== []) {
                $this->routeConfig->addController($class->getName());

                return;
            }
        }
    }
}
