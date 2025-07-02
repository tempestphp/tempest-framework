<?php

namespace Tempest\Mail\Transports\Postmark;

use Symfony\Component\Mailer\Bridge\Postmark\Transport\PostmarkApiTransport;
use Symfony\Component\Mailer\Bridge\Postmark\Transport\PostmarkSmtpTransport;
use Symfony\Component\Mailer\Bridge\Postmark\Transport\PostmarkTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Tempest\Mail\Address;
use Tempest\Mail\MailerConfig;
use UnitEnum;

/**
 * Send emails using Postmark's API or SMTP server.
 */
final class PostmarkConfig implements MailerConfig
{
    public string $transport {
        get => match ($this->sceme) {
            PostmarkConnectionScheme::API => PostmarkApiTransport::class,
            PostmarkConnectionScheme::SMTP => PostmarkSmtpTransport::class,
        };
    }

    public function __construct(
        /**
         * API key used to authenticate to the Postmark API.
         */
        public string $key,

        /**
         * Host used for connecting to the Postmark API.
         */
        public ?string $host = null,

        /**
         * Port used for connecting to the Postmark API.
         */
        public ?int $port = null,

        /**
         * Address from which emails are sent by default.
         */
        public null|string|Address $from = null,

        /**
         * Whether to use Postmark's API or SMTP server.
         */
        public PostmarkConnectionScheme $scheme = PostmarkConnectionScheme::API,
    ) {}

    public function createTransport(): TransportInterface
    {
        return new PostmarkTransportFactory()->create(new Dsn(
            scheme: PostmarkConnectionScheme::API->value,
            user: $this->key,
            host: $this->host ?? 'default',
            port: $this->port,
        ));
    }
}
