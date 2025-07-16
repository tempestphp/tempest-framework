<?php

namespace Tests\Tempest\Integration\Mailer\Fixtures;

use Tempest\Mail\Email;
use Tempest\Mail\Envelope;
use Tempest\Mail\HasTextContent;
use Tempest\View\View;

final class TextEmail implements Email, HasTextContent
{
    public Envelope $envelope {
        get => new Envelope(
            subject: 'This is a text-content email',
            to: $this->to,
            from: $this->from,
        );
    }

    public string|View $html {
        get {
            return $this->text;
        }
    }

    public array $attachments = [];

    public function __construct(
        public string $text = 'Lorem ipsum dolor sit amet',
        private readonly ?string $to = 'jon@doe.co',
        private readonly ?string $from = 'no-reply@tempestphp.com',
    ) {}
}
