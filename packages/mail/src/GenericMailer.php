<?php

namespace Tempest\Mail;

use Symfony\Component\Mailer\Transport\TransportInterface;
use Tempest\EventBus\EventBus;

use function Tempest\Mapper\map;

/**
 * Generic mailer based on Symfony transports.
 */
final readonly class GenericMailer implements Mailer
{
    public function __construct(
        private EventBus $eventBus,
        private TransportInterface $transport,
    ) {}

    public function send(Email $email): void
    {
        $symfonyMail = map($email)
            ->with(EmailToSymfonyEmailMapper::class)
            ->do();

        $this->transport->send($symfonyMail);

        $this->eventBus->dispatch(new EmailWasSent($email));
    }
}
