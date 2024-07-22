<?php

declare(strict_types=1);

namespace Tempest\View\Renderers;

use Jenssegers\Blade\Blade;
use Tempest\Container\Container;
use Tempest\Container\DynamicInitializer;
use Tempest\Container\Singleton;

#[Singleton]
final readonly class BladeInitializer implements DynamicInitializer
{
    public function canInitialize(string $className): bool
    {
        if (! class_exists('\Jenssegers\Blade\Blade')) {
            return false;
        }

        return $className === Blade::class;
    }

    public function initialize(string $className, Container $container): object
    {
        $bladeConfig = $container->get(BladeConfig::class);

        return new Blade(
            viewPaths: $bladeConfig->viewPaths,
            cachePath: $bladeConfig->cachePath,
        );
    }
}
