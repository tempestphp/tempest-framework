<?php

namespace Tempest\Mail;

use Tempest\View\View;

final class GenericEmail implements Email, HasTextContent, HasAttachments
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

    public function __construct(
        public ?string $subject,
        public null|string|array|EmailAddress $to,
        public string|View $html,
        public string|View|null $text = null,
        public null|string|array|EmailAddress $from = null,
        public null|string|array|EmailAddress $cc = null,
        public null|string|array|EmailAddress $bcc = null,
        public null|string|array|EmailAddress $replyTo = null,
        public array $headers = [],
        public EmailPriority $priority = EmailPriority::NORMAL,
        /** @var Attachment[] */
        public array $attachments = [],
    ) {}
}
