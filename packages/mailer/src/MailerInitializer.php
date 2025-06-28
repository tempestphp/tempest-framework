<?php

namespace Tempest\Mail;

use Tempest\Container\Container;
use Tempest\Container\DynamicInitializer;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Mail\MailerConfig;
use Tempest\Reflection\ClassReflector;
use Tempest\View\ViewRenderer;
use UnitEnum;

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
