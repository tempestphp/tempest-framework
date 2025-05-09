<?php

namespace Tempest\Mail;

use Tempest\Container\Container;
use Tempest\Container\DynamicInitializer;
use Tempest\Container\Singleton;
use Tempest\Mail\MailerConfig;
use Tempest\Reflection\ClassReflector;
use Tempest\View\ViewRenderer;

final class MailerInitializer implements DynamicInitializer
{
    public function canInitialize(ClassReflector $class, ?string $tag): bool
    {
        return $class->getType()->matches(Mailer::class);
    }

    #[Singleton]
    public function initialize(ClassReflector $class, ?string $tag, Container $container): Mailer
    {
        return new GenericMailer(
            mailerConfig: $container->get(MailerConfig::class, $tag),
            viewRenderer: $container->get(ViewRenderer::class),
        );
    }
}
