<?php

namespace Tempest\Mail;

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\TransportInterface;

interface MailerConfig
{
    /**
     * The underlying Symfony transport class.
     *
     * @param class-string<Transport>
     */
    public string $transport {
        get;
    }

    /**
     * Creates the transport.
     */
    public function createTransport(): TransportInterface;
}
