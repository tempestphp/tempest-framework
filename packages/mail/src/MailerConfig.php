<?php

namespace Tempest\Mail;

use Symfony\Component\Mailer\Transport\TransportInterface;

interface MailerConfig
{
    /**
     * The underlying Symfony transport class.
     *
     * @var class-string<TransportInterface>
     */
    public string $transport {
        get;
    }

    /**
     * Creates the transport.
     */
    public function createTransport(): TransportInterface;
}
