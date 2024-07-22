<?php

declare(strict_types=1);

namespace Tempest\Http;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\View\ViewRenderer;

#[Singleton]
final class ResponseSenderInitializer implements Initializer
{
    public function initialize(Container $container): ResponseSender
    {
        return new GenericResponseSender($container->get(ViewRenderer::class));
    }
}
