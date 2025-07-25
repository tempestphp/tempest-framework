<?php

namespace Tempest\Mail\Testing;

use Tempest\EventBus\EventBus;
use Tempest\Mail\Email;
use Tempest\Mail\EmailWasSent;
use Tempest\Mail\Mailer;

final class TestingMailer implements Mailer
{
    public function __construct(
        private readonly ?EventBus $eventBus = null,
    ) {}

    /**
     * List of emails that would have been sent.
     *
     * @var array<Email>
     */
    private(set) array $sent = [];

    public function send(Email $email): void
    {
        $this->sent[] = $email;

        $this->eventBus?->dispatch(new EmailWasSent($email));
    }
}
