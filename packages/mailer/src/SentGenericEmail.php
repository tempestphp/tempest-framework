<?php

namespace Tempest\Mail;

use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mime\Address as SymfonyAddress;
use Symfony\Component\Mime\Email as SymfonyEmail;
use Symfony\Component\Mime\Part\DataPart;
use Tempest\Support\Arr;

final class SentGenericEmail implements SentEmail
{
    public string $id {
        get => $this->sent->getMessageId();
    }

    public array $headers {
        get => $this->symfonyEmail->getHeaders()->toArray();
    }

    public array $from {
        get => Arr\map_iterable(
            array: $this->symfonyEmail->getFrom(),
            map: fn (SymfonyAddress $address) => new Address($address->getAddress(), $address->getName()),
        );
    }

    public array $to {
        get => Arr\map_iterable(
            array: $this->symfonyEmail->getTo(),
            map: fn (SymfonyAddress $address) => new Address($address->getAddress(), $address->getName()),
        );
    }

    public array $attachments {
        get => Arr\map_iterable(
            array: $this->symfonyEmail->getAttachments(),
            map: fn (DataPart $attachment) => new Attachment(
                resolve: fn () => $attachment->getBody(),
                name: $attachment->getFilename(),
                contentType: $attachment->getMediaType() . '/' . $attachment->getMediaSubtype(),
            ),
        );
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
