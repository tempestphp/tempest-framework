<?php

namespace Tempest\Mail\Testing;

use Tempest\Container\Container;
use Tempest\Container\GenericContainer;
use Tempest\Mail\Mailer;
use Tempest\Mail\MailerConfig;
use Tempest\Mail\MailerInitializer;
use Tempest\Mail\Transports\NullMailerConfig;
use Tempest\Mail\Transports\Smtp\SmtpMailerConfig;
use Tempest\Support\Str;
use Tempest\View\ViewRenderer;
use UnitEnum;

final class MailerTester
{
    public function __construct(
        private Container $container,
    ) {}

    /**
     * Forces the usage of a testing mailer.
     */
    public function fake(null|string|UnitEnum $tag = null): TestingMailer
    {
        if (! $this->container->has(MailerConfig::class, $tag)) {
            $this->container->config(new NullMailerConfig($tag));
        }

        $mailer = new TestingMailer(
            tag: match (true) {
                is_string($tag) => Str\to_kebab_case($tag),
                $tag instanceof UnitEnum => Str\to_kebab_case($tag->name),
                default => 'default',
            },
            mailerConfig: $this->container->get(MailerConfig::class, $tag),
            viewRenderer: $this->container->get(ViewRenderer::class),
        );

        $this->container->singleton(Mailer::class, $mailer, $tag);

        return $mailer;
    }

    /**
     * Prevents mailers from being actually used.
     */
    public function preventRealUsage(): void
    {
        if (! ($this->container instanceof GenericContainer)) {
            throw new \RuntimeException('Container is not a GenericContainer, unable to prevent usage without fake.');
        }

        $this->container->unregister(Mailer::class, tagged: true);
        $this->container->removeInitializer(MailerInitializer::class);
        $this->container->addInitializer(RestrictedMailerInitializer::class);
    }
}
