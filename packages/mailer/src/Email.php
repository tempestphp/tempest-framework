<?php

namespace Tempest\Mail;

use Tempest\View\View;

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
     * The content of the email can be a path to a view file, raw HTML, or a View object
     */
    public string|View $content {
        get;
    }

    /**
     * @var \Tempest\Mail\Attachment[] $attachments
     */
    public array $attachments {
        get;
    }
}
