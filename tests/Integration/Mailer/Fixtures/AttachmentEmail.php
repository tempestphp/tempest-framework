<?php

namespace Tests\Tempest\Integration\Mailer\Fixtures;

use Tempest\Mail\Email;
use Tempest\Mail\Envelope;
use Tempest\Mail\HasAttachments;
use Tempest\View\View;

final class AttachmentEmail implements Email, HasAttachments
{
    public Envelope $envelope {
        get => new Envelope(subject: 'With attachment', to: 'jon@doe.com', from: 'jane@doe.com');
    }

    public string|View $html {
        get => 'Hello world';
    }

    public function __construct(
        public array $attachments,
    ) {}
}
