<?php

namespace Tempest\Mail;

use Symfony\Component\Mailer\Transport\TransportInterface;
use Tempest\EventBus\EventBus;
use Tempest\Mail\Exceptions\MailerTransportWasMissing;
use Tempest\View\ViewRenderer;

use function Tempest\map;

/**
 * Generic mailer based on Symfony transports.
 */
final class GenericMailer implements Mailer
{
    public function __construct(
        private readonly MailerConfig $mailerConfig,
        private readonly ViewRenderer $viewRenderer,
        private readonly ?EventBus $eventBus = null,
        private ?TransportInterface $transport = null,
    ) {
        $this->transport ??= $this->createTransport();
    }

    public function send(Email $email): SentEmail
    {
        $symfonyEmail = map($email)
            ->with(fn (Email $from) => new EmailToSymfonyEmailMapper($this->mailerConfig, $this->viewRenderer)->map($from, null))
            ->do();

        $this->eventBus?->dispatch(new EmailWasSent($email));

        return new SentGenericEmail(
            original: $email,
            symfonyEmail: $symfonyEmail,
            sent: $this->transport->send($symfonyEmail),
        );
    }

    private function createTransport(): TransportInterface
    {
        $this->assertTransportInstalled($this->mailerConfig->transport);

        return $this->mailerConfig->createTransport();
    }

    private function assertTransportInstalled(string $transport): void
    {
        if (! class_exists($transport)) {
            throw new MailerTransportWasMissing($transport);
        }
    }
}
