<?php

declare(strict_types=1);

namespace Tempest\Mail;

use Symfony\Component\Mime\Address as SymfonyAddress;
use Symfony\Component\Mime\Email as SymfonyEmail;
use Tempest\Mail\Exceptions\ExpeditorWasMissing;
use Tempest\Mail\Exceptions\RecipientWasMissing;
use Tempest\Mapper\Mapper;
use Tempest\Support\Arr;
use Tempest\View\View;
use Tempest\View\ViewRenderer;

use function Tempest\Support\arr;

final readonly class EmailToSymfonyEmailMapper implements Mapper
{
    public function __construct(
        private readonly MailerConfig $mailerConfig,
        private readonly ViewRenderer $viewRenderer,
    ) {}

    public function canMap(mixed $from, mixed $to): bool
    {
        if ($from instanceof Email) {
            return true;
        }

        return false;
    }

    public function map(mixed $from, mixed $to): SymfonyEmail
    {
        /** @var Email $email */
        $email = $from;
        $symfonyEmail = new SymfonyEmail();

        foreach ($email->envelope->headers as $key => $value) {
            $symfonyEmail->getHeaders()->addHeader($key, $value);
        }

        if ($email->envelope->from) {
            $symfonyEmail->from(...$this->convertAddresses($email->envelope->from));
        } elseif ($this->mailerConfig->from) {
            $symfonyEmail->from($this->mailerConfig->from);
        } else {
            throw new ExpeditorWasMissing();
        }

        if ($email->envelope->to) {
            $symfonyEmail->to(...$this->convertAddresses($email->envelope->to));
        } else {
            throw new RecipientWasMissing();
        }

        if ($email->envelope->cc) {
            $symfonyEmail->cc(...$this->convertAddresses($email->envelope->cc));
        }

        if ($email->envelope->bcc) {
            $symfonyEmail->bcc(...$this->convertAddresses($email->envelope->bcc));
        }

        if ($email->envelope->replyTo) {
            $symfonyEmail->replyTo(...$this->convertAddresses($email->envelope->replyTo));
        }

        if ($email->envelope->subject) {
            $symfonyEmail->subject($email->envelope->subject);
        }

        $symfonyEmail->priority($email->envelope->priority->value);

        if ($email->content->text) {
            $symfonyEmail->text($email->content->text);
        }

        if ($email->content->html instanceof View) {
            $symfonyEmail->html($this->viewRenderer->render($email->content->html));
        } elseif ($email->content->html) {
            $symfonyEmail->html($email->content->html);
        }

        /** @var Attachment $attachment */
        foreach (Arr\wrap($email->content->attachments) as $attachment) {
            $symfonyEmail->attach(($attachment->resolve)(), $attachment->name, $attachment->contentType);
        }

        return $symfonyEmail;
    }

    private function convertAddresses(null|string|array|Address $addresses): array
    {
        return arr($addresses)
            ->map(fn (string|Address|SymfonyAddress $address) => match (true) {
                $address instanceof SymfonyAddress => $address,
                $address instanceof Address => new SymfonyAddress($address->email, $address->name),
                is_string($address) => SymfonyAddress::create($address),
                default => null,
            })
            ->filter()
            ->toArray();
    }
}
