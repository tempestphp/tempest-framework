<?php

namespace Tempest\Vite;

use Tempest\Container\Container;
use Tempest\Container\DynamicInitializer;
use Tempest\Core\AppConfig;
use Tempest\Reflection\ClassReflector;
use Tempest\Vite\TagCompiler\TagCompiler;

final class ViteInitializer implements DynamicInitializer
{
    public function canInitialize(ClassReflector $class, ?string $tag = null): bool
    {
        return $class->getName() === Vite::class;
    }

    public function initialize(ClassReflector $class, Container $container, ?string $tag = null): Vite
    {
        return new Vite(
            viteConfig: $container->get(ViteConfig::class, $tag),
            appConfig: $container->get(AppConfig::class),
            container: $container,
            tagCompiler: $container->get(TagCompiler::class),
        );
    }
}
