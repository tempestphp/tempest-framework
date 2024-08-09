<?php

declare(strict_types=1);

namespace Tempest\View\Renderers;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\View\ViewConfig;
use Tempest\View\ViewRenderer;

final readonly class ViewRendererInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): ViewRenderer
    {
        $viewConfig = $container->get(ViewConfig::class);

        return $container->get($viewConfig->rendererClass);
    }
}
