<?php

namespace Tempest\View\Initializers;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Core\Environment;
use Tempest\View\Elements\ElementFactory;
use Tempest\View\ViewConfig;

final class ElementFactoryInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): ElementFactory
    {
        return new ElementFactory(
            viewConfig: $container->get(ViewConfig::class),
            environment: $container->get(Environment::class),
        );
    }
}
