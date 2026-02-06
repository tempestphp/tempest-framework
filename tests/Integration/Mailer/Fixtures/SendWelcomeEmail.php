<?php

namespace Tests\Tempest\Integration\Mailer\Fixtures;

use Tempest\Mail\Email;
use Tempest\Mail\Envelope;
use Tempest\View\View;

use function Tempest\View\view;

final class SendWelcomeEmail implements Email
{
    public Envelope $envelope {
        get => new Envelope(
            subject: "Welcome {$this->fullName}",
            to: $this->address,
            from: 'no-reply@tempestphp.com',
        );
    }

    public string|View $html {
        get {
            return view(__DIR__ . '/welcome.view.php', fullName: $this->fullName);
        }
    }

    public array $attachments = [];

    public function __construct(
        private readonly string $address,
        private readonly string $fullName,
    ) {}
}
