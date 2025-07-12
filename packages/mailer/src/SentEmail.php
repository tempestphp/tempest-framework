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
     * The addresses the email was sent from.
     *
     * @var Address[]
     */
    public array $from {
        get;
    }

    /**
     * The addresses the email was sent to.
     *
     * @var Address[]
     */
    public array $to {
        get;
    }

    /**
     * The attachments included in the email.
     *
     * @var Attachment[]
     */
    public array $attachments {
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
