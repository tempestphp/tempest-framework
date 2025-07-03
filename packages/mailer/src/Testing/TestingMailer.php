<?php

namespace Tempest\Mail\Testing;

use Tempest\EventBus\EventBus;
use Tempest\Mail\Email;
use Tempest\Mail\EmailSent;
use Tempest\Mail\EmailToSymfonyEmailMapper;
use Tempest\Mail\Mailer;
use Tempest\Mail\MailerConfig;
use Tempest\View\ViewRenderer;

use function Tempest\map;

final class TestingMailer implements Mailer
{
    public function __construct(
        private readonly MailerConfig $mailerConfig,
        private readonly ViewRenderer $viewRenderer,
        private readonly ?EventBus $eventBus = null,
    ) {}

    /**
     * List of emails that would have been sent.
     *
     * @var array<Email>
     */
    private(set) array $sent = [];

    public function send(Email $email): SentTestingEmail
    {
        $this->sent[] = $email;

        $symfonyEmail = map($email)
            ->with(fn (Email $from) => new EmailToSymfonyEmailMapper($this->mailerConfig, $this->viewRenderer)->map($from, null))
            ->do();

        $this->eventBus?->dispatch(new EmailSent($email));

        return new SentTestingEmail(
            original: $email,
            symfonyEmail: $symfonyEmail,
        );
    }
}
