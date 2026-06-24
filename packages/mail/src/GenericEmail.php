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
        public string|array|EmailAddress|null $to,
        public string|View $html,
        public string|View|null $text = null,
        public string|array|EmailAddress|null $from = null,
        public string|array|EmailAddress|null $cc = null,
        public string|array|EmailAddress|null $bcc = null,
        public string|array|EmailAddress|null $replyTo = null,
        public array $headers = [],
        public EmailPriority $priority = EmailPriority::NORMAL,
        /** @var Attachment[] */
        public array $attachments = [],
    ) {}
}
