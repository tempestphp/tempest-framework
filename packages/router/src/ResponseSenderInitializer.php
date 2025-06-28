<?php

declare(strict_types=1);

namespace Tempest\Router;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Http\Request;
use Tempest\View\ViewRenderer;

final class ResponseSenderInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): ResponseSender
    {
        return new GenericResponseSender(
            request: $container->get(Request::class),
            viewRenderer: $container->get(ViewRenderer::class),
        );
    }
}
