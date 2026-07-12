<?php

namespace Tempest\Mail;

/**
 * Represents the envelope of an email.
 */
final class Envelope
{
    public function __construct(
        public ?string $subject,
        public string|array|EmailAddress|null $to,
        public string|array|EmailAddress|null $from = null,
        public string|array|EmailAddress|null $cc = null,
        public string|array|EmailAddress|null $bcc = null,
        public string|array|EmailAddress|null $replyTo = null,
        public array $headers = [],
        public EmailPriority $priority = EmailPriority::NORMAL,
    ) {}
}
