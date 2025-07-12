<?php

namespace Tempest\Mail;

/**
 * Represents an email.
 */
interface Email
{
    /**
     * The envelope of the email.
     */
    public Envelope $envelope {
        get;
    }

    /**
     * The content of the email.
     */
    public Content $content {
        get;
    }
}
