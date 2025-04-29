<?php

declare(strict_types=1);

namespace Tempest\View\Renderers;

use Tempest\Blade\Blade;
use Tempest\Container\Container;
use Tempest\Container\DynamicInitializer;
use Tempest\Container\Singleton;
use Tempest\Container\Tag;
use Tempest\Reflection\ClassReflector;

use function Tempest\internal_storage_path;

final readonly class BladeInitializer implements DynamicInitializer
{
    public function canInitialize(ClassReflector $class, ?string $tag): bool
    {
        if (! class_exists(Blade::class)) {
            return false;
        }

        return $class->getName() === Blade::class;
    }

    #[Singleton]
    public function initialize(ClassReflector $class, ?string $tag, Container $container): object
    {
        $bladeConfig = $container->get(BladeConfig::class);

        return new Blade(
            viewPaths: $bladeConfig->viewPaths,
            cachePath: internal_storage_path($bladeConfig->cachePath ?? 'cache/blade'),
        );
    }
}
