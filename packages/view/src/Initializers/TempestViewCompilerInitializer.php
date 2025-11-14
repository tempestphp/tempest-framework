<?php

namespace Tempest\View\Initializers;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Core\Kernel;
use Tempest\View\Attributes\AttributeFactory;
use Tempest\View\Elements\ElementFactory;
use Tempest\View\Parser\TempestViewCompiler;

final readonly class TempestViewCompilerInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): TempestViewCompiler
    {
        return new TempestViewCompiler(
            elementFactory: $container->get(ElementFactory::class),
            attributeFactory: $container->get(AttributeFactory::class),
            discoveryLocations: $container->get(Kernel::class)->discoveryLocations,
        );
    }
}
