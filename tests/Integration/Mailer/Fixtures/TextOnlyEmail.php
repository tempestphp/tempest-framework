<?php

namespace Tests\Tempest\Integration\Mailer\Fixtures;

use Tempest\Mail\Content;
use Tempest\Mail\Email;
use Tempest\Mail\Envelope;

final class TextOnlyEmail implements Email
{
    public Envelope $envelope {
        get => new Envelope(
            subject: 'This is a text-only email',
            to: $this->to,
            from: $this->from,
        );
    }

    public Content $content {
        get => new Content(
            text: $this->text,
        );
    }

    public function __construct(
        private readonly ?string $text = 'Lorem ipsum dolor sit amet',
        private readonly ?string $to = 'jon@doe.co',
        private readonly ?string $from = 'no-reply@tempestphp.com',
    ) {}
}
