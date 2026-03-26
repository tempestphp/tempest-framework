<?php

namespace Tempest\Mail\Testing;

use Tempest\Mail\Email;
use Throwable;

final readonly class FailedEmail
{
    public function __construct(
        public Email $email,
        public Throwable $exception,
    ) {}
}
