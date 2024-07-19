<?php

namespace Tempest\View\Renderers;

use Jenssegers\Blade\Blade;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

#[Singleton]
final readonly class BladeInitializer implements Initializer
{
    public function initialize(Container $container): Blade
    {
        $bladeConfig = $container->get(BladeConfig::class);

        return new Blade(
            viewPaths: $bladeConfig->viewPaths,
            cachePath: $bladeConfig->cachePath,
        );
    }
}