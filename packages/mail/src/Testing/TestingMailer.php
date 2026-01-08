<?php

namespace Tempest\Mail\Testing;

use Tempest\EventBus\EventBus;
use Tempest\Mail\Email;
use Tempest\Mail\EmailWasSent;
use Tempest\Mail\Mailer;

use function Tempest\Container\get;

final class TestingMailer implements Mailer
{
    private ?EventBus $eventBus {
        get => get(className: EventBus::class);
    }

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
