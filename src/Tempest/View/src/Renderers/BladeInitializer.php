<?php

declare(strict_types=1);

namespace Tempest\View\Renderers;

use Jenssegers\Blade\Blade;
use Tempest\Container\Container;
use Tempest\Container\DynamicInitializer;
use Tempest\Container\Singleton;
use Tempest\Support\Reflection\ClassReflector;

final readonly class BladeInitializer implements DynamicInitializer
{
    public function canInitialize(ClassReflector $class): bool
    {
        if (! class_exists(Blade::class)) {
            return false;
        }

        return $class->getName() === Blade::class;
    }

    #[Singleton]
    public function initialize(ClassReflector $class, Container $container): object
    {
        $bladeConfig = $container->get(BladeConfig::class);

        return new Blade(
            viewPaths: $bladeConfig->viewPaths,
            cachePath: $bladeConfig->cachePath,
        );
    }
}
