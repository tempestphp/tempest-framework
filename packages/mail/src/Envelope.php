<?php

namespace Tempest\Mail;

/**
 * Represents the envelope of an email.
 */
final class Envelope
{
    public function __construct(
        public ?string $subject,
        public null|string|array|EmailAddress $to,
        public null|string|array|EmailAddress $from = null,
        public null|string|array|EmailAddress $cc = null,
        public null|string|array|EmailAddress $bcc = null,
        public null|string|array|EmailAddress $replyTo = null,
        public array $headers = [],
        public EmailPriority $priority = EmailPriority::NORMAL,
    ) {}
}
