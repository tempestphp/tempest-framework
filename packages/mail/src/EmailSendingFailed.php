<?php

namespace Tempest\Mail;

use Throwable;

/**
 * An email failed to send.
 */
final readonly class EmailSendingFailed
{
    public function __construct(
        public Email $email,
        public Throwable $exception,
    ) {}
}
