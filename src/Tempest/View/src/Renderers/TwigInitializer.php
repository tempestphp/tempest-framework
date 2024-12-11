<?php

declare(strict_types=1);

namespace Tempest\View\Renderers;

use Tempest\Container\Container;
use Tempest\Container\DynamicInitializer;
use Tempest\Container\Singleton;
use Tempest\Reflection\ClassReflector;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

final readonly class TwigInitializer implements DynamicInitializer
{
    public function canInitialize(ClassReflector $class): bool
    {
        if (! class_exists(Environment::class)) {
            return false;
        }

        return $class->getName() === Environment::class;
    }

    #[Singleton]
    public function initialize(ClassReflector $class, Container $container): object
    {
        $twigConfig = $container->get(TwigConfig::class);
        $twigLoader = new FilesystemLoader($twigConfig->viewPaths);

        return new Environment($twigLoader, (array) $twigConfig);
    }
}
