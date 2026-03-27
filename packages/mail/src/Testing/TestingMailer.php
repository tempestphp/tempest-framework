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
     * List of emails that would have failed to send.
     *
     * @var array<FailedEmail>
     */
    private(set) array $failed = [];

    /**
     * If set, the next send call will fail with this exception.
     */
    private(set) ?Throwable $exception = null;

    public function send(Email $email): void
    {
        if ($this->exception instanceof Throwable) {
            $failure = new FailedEmail(
                email: $email,
                exception: $this->exception,
            );

            $this->failed[] = $failure;
            $this->exception = null;

            $this->eventBus?->dispatch(event: new EmailSendingFailed(
                email: $email,
                exception: $failure->exception,
            ));

            throw $failure->exception;
        }

        $this->sent[] = $email;

        $this->eventBus?->dispatch(event: new EmailWasSent(email: $email));
    }

    /**
     * Simulates a transport failure for the next send call.
     */
    public function shouldFail(?Throwable $exception = null): void
    {
        $this->exception = $exception ?? new TransportException(message: 'Test transport failure');
    }
}
