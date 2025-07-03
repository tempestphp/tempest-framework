<?php

namespace Tempest\Mail\Transports;

use Symfony\Component\Mailer\Transport\NullTransport;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Tempest\Mail\Address;
use Tempest\Mail\MailerConfig;

/**
 * Do not send emails.
 */
final class NullMailerConfig implements MailerConfig, ProvidesDefaultSender
{
    public string $transport = NullTransport::class;

    public function __construct(
        public null|string|Address $defaultSender = null,
    ) {}

    public function createTransport(): TransportInterface
    {
        return new NullTransport();
    }
}
