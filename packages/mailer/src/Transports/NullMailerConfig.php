<?php

namespace Tempest\Mail\Transports;

use Symfony\Component\Mailer\Transport\NullTransport;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Address;
use Tempest\Mail\MailerConfig;
use UnitEnum;

/**
 * Do not send emails.
 */
final class NullMailerConfig implements MailerConfig
{
    public string $transport = NullTransport::class;

    public function __construct(
        public null|string|UnitEnum $tag = null,
        public null|string|Address $from = null,
    ) {}

    public function createTransport(): TransportInterface
    {
        return new NullTransport();
    }
}
