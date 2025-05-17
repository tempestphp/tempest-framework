<?php

namespace Tempest\Mail;

use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mime\Email as SymfonyEmail;

final class SentGenericEmail implements SentEmail
{
    public string $id {
        get => $this->sent->getMessageId();
    }

    public array $headers {
        get => $this->symfonyEmail->getHeaders()->toArray();
    }

    public string $raw {
        get => $this->sent->getMessage()->toString();
    }

    public string $debug {
        get => $this->sent->getDebug();
    }

    public function __construct(
        public readonly Email $original,
        public readonly SymfonyEmail $symfonyEmail,
        private readonly SentMessage $sent,
    ) {}
}
