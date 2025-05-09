<?php

namespace Tempest\Mail\Testing;

use Tempest\Mail\Email;
use Tempest\Mail\Exceptions\ForbiddenMailerUsageException;
use Tempest\Mail\Mailer;
use Tempest\Mail\SentEmail;

final class RestrictedMailer implements Mailer
{
    public function __construct(
        private ?string $tag = null,
    ) {}

    public function send(Email $email): SentEmail
    {
        throw new ForbiddenMailerUsageException($this->tag);
    }
}
