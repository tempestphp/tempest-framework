<?php

namespace Tempest\Mail;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Mail\MailerConfig;
use Tempest\View\ViewRenderer;

final class MailerInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): Mailer
    {
        return new GenericMailer(
            mailerConfig: $container->get(MailerConfig::class),
            viewRenderer: $container->get(ViewRenderer::class),
        );
    }
}
