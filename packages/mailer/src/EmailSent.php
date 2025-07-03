<?php

namespace Tempest\Mail;

/**
 * An email has been sent.
 */
final readonly class EmailSent
{
    public function __construct(
        public Email $email,
    ) {}
}
