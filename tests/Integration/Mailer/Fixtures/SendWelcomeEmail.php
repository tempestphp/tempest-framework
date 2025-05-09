<?php

namespace Tests\Tempest\Integration\Mailer\Fixtures;

use Tempest\Mail\Content;
use Tempest\Mail\Email;
use Tempest\Mail\Envelope;

use function Tempest\view;

final class SendWelcomeEmail implements Email
{
    public Envelope $envelope {
        get => new Envelope(
            subject: "Welcome {$this->fullName}",
            to: $this->address,
            from: 'no-reply@tempestphp.com',
        );
    }

    public Content $content {
        get => new Content(
            html: view('./welcome.view.php', fullName: $this->fullName),
        );
    }

    public function __construct(
        private readonly string $address,
        private readonly string $fullName,
    ) {}
}
