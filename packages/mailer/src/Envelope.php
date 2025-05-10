<?php

namespace Tempest\Mail;

/**
 * Represents the envelope of an email.
 */
final class Envelope
{
    public function __construct(
        public ?string $subject,
        public null|string|array|Address $to,
        public null|string|array|Address $from = null,
        public null|string|array|Address $cc = null,
        public null|string|array|Address $bcc = null,
        public null|string|array|Address $replyTo = null,
        public Priority $priority = Priority::NORMAL,
    ) {}
}
