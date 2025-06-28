<?php

namespace Tempest\Mail;

use Symfony\Component\Mailer\Transport\TransportInterface;

interface MailerConfig
{
    /**
     * The transport class.
     */
    public string $transport {
        get;
    }

    /**
     * The default address from which emails will be sent.
     */
    public null|string|Address $from {
        get;
    }

    /**
     * Creates the transport.
     */
    public function createTransport(): TransportInterface;
}
