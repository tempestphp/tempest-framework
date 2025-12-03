<?php

namespace Tempest\Mail\Transports\Postmark;

use Symfony\Component\Mailer\Bridge\Postmark\Transport\PostmarkApiTransport;
use Symfony\Component\Mailer\Bridge\Postmark\Transport\PostmarkSmtpTransport;
use Symfony\Component\Mailer\Bridge\Postmark\Transport\PostmarkTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Tempest\Mail\EmailAddress;
use Tempest\Mail\MailerConfig;
use Tempest\Mail\Transports\ProvidesDefaultSender;

/**
 * Send emails using Postmark's API or SMTP server.
 */
final class PostmarkConfig implements MailerConfig, ProvidesDefaultSender
{
    public string $transport {
        get => match ($this->scheme) {
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
        public null|string|EmailAddress $defaultSender = null,

        /**
         * Whether to use Postmark's API or SMTP server.
         */
        public PostmarkConnectionScheme $scheme = PostmarkConnectionScheme::API,
    ) {}

    public function createTransport(): TransportInterface
    {
        return new PostmarkTransportFactory()->create(new Dsn(
            scheme: $this->scheme->value,
            host: $this->host ?? 'default',
            user: $this->key,
            port: $this->port,
        ));
    }
}
