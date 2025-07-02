<?php

namespace Tempest\Mail;

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\TransportInterface;

interface MailerConfig
{
    /**
     * The transport class.
     *
     * @param class-string<Transport>
     */
    public string $transport {
        get;
    }

    /**
     * The default address from which emails will be sent.
     */
    public null|string|Address $defaultExpeditor {
        get;
    }

    /**
     * Creates the transport.
     */
    public function createTransport(): TransportInterface;
}
