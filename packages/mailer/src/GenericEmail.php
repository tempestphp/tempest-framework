<?php

namespace Tempest\Mail;

use Tempest\View\View;

/**
 * Represents a generic email.
 */
final class GenericEmail implements Email
{
    public Envelope $envelope {
        get => new Envelope(
            subject: $this->subject,
            to: $this->to,
            from: $this->from,
            cc: $this->cc,
            bcc: $this->bcc,
            replyTo: $this->replyTo,
            headers: $this->headers,
            priority: $this->priority,
        );
    }

    public Content $content {
        get => new Content(
            html: $this->html,
            text: $this->text,
            attachments: $this->attachments,
        );
    }

    public function __construct(
        public ?string $subject,
        public null|string|array|Address $to,
        public null|string|array|Address $from = null,
        public null|string|array|Address $cc = null,
        public null|string|array|Address $bcc = null,
        public null|string|array|Address $replyTo = null,
        public array $headers = [],
        public EmailPriority $priority = EmailPriority::NORMAL,
        public null|string|View $html = null,
        public ?string $text = null,
        public array $attachments = [],
    ) {}

    /**
     * Builds a new generic email.
     */
    public static function build(): EmailBuilder
    {
        return new EmailBuilder();
    }
}
