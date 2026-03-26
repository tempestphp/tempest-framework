<?php

namespace Tempest\Mail\Testing;

use Symfony\Component\Mailer\Exception\TransportException;
use Tempest\EventBus\EventBus;
use Tempest\Mail\Email;
use Tempest\Mail\EmailSendingFailed;
use Tempest\Mail\EmailWasSent;
use Tempest\Mail\Mailer;
use Throwable;

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

    /**
     * List of emails that failed to send.
     *
     * @var array<Email>
     */
    private(set) array $failed = [];

    private ?Throwable $failException = null;

    public function send(Email $email): void
    {
        if ($this->failException !== null) {
            $exception = $this->failException;
            $this->failException = null;
            $this->failed[] = $email;

            $this->eventBus?->dispatch(event: new EmailSendingFailed(
                email: $email,
                exception: $exception,
            ));

            throw $exception;
        }

        $this->sent[] = $email;

        $this->eventBus?->dispatch(event: new EmailWasSent(email: $email));
    }

    /**
     * Simulates a transport failure for the next send call.
     */
    public function shouldFail(?Throwable $exception = null): void
    {
        $this->failException = $exception ?? new TransportException(message: 'Test transport failure');
    }
}
