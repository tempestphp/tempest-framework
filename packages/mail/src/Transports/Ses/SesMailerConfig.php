<?php

namespace Tempest\Mail\Transports\Ses;

use Symfony\Component\Mailer\Bridge\Amazon\Transport\SesApiAsyncAwsTransport;
use Symfony\Component\Mailer\Bridge\Amazon\Transport\SesHttpAsyncAwsTransport;
use Symfony\Component\Mailer\Bridge\Amazon\Transport\SesTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Tempest\Mail\EmailAddress;
use Tempest\Mail\MailerConfig;
use Tempest\Mail\Transports\ProvidesDefaultSender;

/**
 * Send emails using Amazon SES.
 */
final class SesMailerConfig implements MailerConfig, ProvidesDefaultSender
{
    public string $transport {
        get => match ($this->scheme) {
            SesConnectionScheme::API => SesApiAsyncAwsTransport::class,
            SesConnectionScheme::HTTP => SesHttpAsyncAwsTransport::class,
        };
    }

    public function __construct(
        /**
         * Access key used for authenticating to the SES API.
         */
        public string $accessKey,

        /**
         * Secret key used for authenticating to the SES API.
         */
        public string $secretKey,

        /**
         * Region configured in your SES account.
         */
        public string $region,

        /**
         * An optional endpoint to use instead of the default one.
         */
        public ?string $host = null,

        /**
         * An optional Amazon SES session token.
         */
        public ?string $sessionToken = null,

        /**
         * Address from which emails are sent by default.
         */
        public null|string|EmailAddress $defaultSender = null,

        /**
         * Whether to use Amazon SES's API or async HTTP transport.
         */
        public SesConnectionScheme $scheme = SesConnectionScheme::HTTP,
    ) {}

    public function createTransport(): TransportInterface
    {
        return new SesTransportFactory()->create(new Dsn(
            scheme: $this->scheme->value,
            host: $this->host ?? 'default',
            user: $this->accessKey,
            password: $this->secretKey,
            options: [
                'region' => $this->region,
                'session_token' => $this->sessionToken,
            ],
        ));
    }
}
