<?php

declare(strict_types=1);

namespace Tempest\Mail;

use Symfony\Component\Mime\Email as SymfonyEmail;
use Tempest\Mail\Exceptions\MissingExpeditorAddressException;
use Tempest\Mail\Exceptions\MissingRecipientAddressException;
use Tempest\Mapper\Mapper;
use Tempest\Support\Arr;
use Tempest\View\View;
use Tempest\View\ViewRenderer;

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

        if ($email->envelope->from) {
            $symfonyEmail->from(...Arr\wrap($email->envelope->from));
        } elseif ($this->mailerConfig->from) {
            $symfonyEmail->from($this->mailerConfig->from);
        } else {
            throw new MissingExpeditorAddressException();
        }

        if ($email->envelope->to) {
            $symfonyEmail->to(...Arr\wrap($email->envelope->to));
        } else {
            throw new MissingRecipientAddressException();
        }

        if ($email->envelope->cc) {
            $symfonyEmail->cc(...Arr\wrap($email->envelope->cc));
        }

        if ($email->envelope->bcc) {
            $symfonyEmail->bcc(...Arr\wrap($email->envelope->bcc));
        }

        if ($email->envelope->replyTo) {
            $symfonyEmail->replyTo(...Arr\wrap($email->envelope->replyTo));
        }

        if ($email->envelope->subject) {
            $symfonyEmail->subject($email->envelope->subject);
        }

        if ($email->envelope->priority) {
            $symfonyEmail->priority($email->envelope->priority->value);
        }

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
}
