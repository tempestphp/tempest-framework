<?php

namespace Tempest\Mail;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\EventBus\EventBus;
use Tempest\Mail\MailerConfig;

final class MailerInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): Mailer
    {
        return new GenericMailer(
            eventBus: $container->get(EventBus::class),
            transport: $container->get(MailerConfig::class)->createTransport(),
        );
    }
}
