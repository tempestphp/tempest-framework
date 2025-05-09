<?php

namespace Tempest\Mail;

interface SentEmail
{
    /**
     * An identifier for this email.
     */
    public string $id {
        get;
    }

    /**
     * The raw content of the email.
     */
    public string $raw {
        get;
    }

    /**
     * Protocol logs.
     */
    public string $debug {
        get;
    }

    /**
     * Email headers.
     */
    public array $headers {
        get;
    }
}
