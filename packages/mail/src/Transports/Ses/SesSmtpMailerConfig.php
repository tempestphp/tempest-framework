<?php

namespace Tempest\Mail\Transports\Ses;

use Symfony\Component\Mailer\Bridge\Amazon\Transport\SesApiAsyncAwsTransport;
use Symfony\Component\Mailer\Bridge\Amazon\Transport\SesHttpAsyncAwsTransport;
use Symfony\Component\Mailer\Bridge\Amazon\Transport\SesSmtpTransport;
use Symfony\Component\Mailer\Bridge\Amazon\Transport\SesTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\NullTransport;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Tempest\Mail\Address;
use Tempest\Mail\MailerConfig;
use UnitEnum;

/**
 * Send emails using Amazon SES.
 */
final class SesSmtpMailerConfig implements MailerConfig
{
    public string $transport = SesSmtpTransport::class;

    public function __construct(
        /**
         * Access key used for authenticating to the SES API.
         */
        public string $username,

        /**
         * Secret key used for authenticating to the SES API.
         */
        public string $password,

        /**
         * Region configured in your SES account.
         */
        public string $region,

        /**
         * An optional endpoint to use instead of the default one.
         */
        public ?string $host = null,

        /**
         * The minimum number of seconds between two messages required to ping the server.
         */
        public ?int $pingThreshold = null,

        /**
         * Address from which emails are sent by default.
         */
        public null|string|Address $from = null,

        /**
         * Whether to use Amazon SES's API or SMTP server.
         */
        public SesConnectionScheme $scheme = SesConnectionScheme::API,
    ) {}

    public function createTransport(): TransportInterface
    {
        return new SesTransportFactory()->create(new Dsn(
            scheme: 'ses+smtp',
            user: $this->username,
            password: $this->password,
            host: $this->host ?? 'default',
            options: [
                'ping_threshold' => $this->pingThreshold,
                'region' => $this->region,
            ],
        ));
    }
}
