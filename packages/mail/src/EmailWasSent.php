<?php

namespace Tempest\Mail;

/**
 * An email has been sent.
 */
final readonly class EmailWasSent
{
    public function __construct(
        public Email $email,
    ) {}
}
