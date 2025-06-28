<?php

namespace Tempest\Mail\Transports\Smtp;

use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransportFactory;
use Symfony\Component\Mailer\Transport\Smtp\SmtpTransport;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Tempest\Mail\Address;
use Tempest\Mail\MailerConfig;
use UnitEnum;

/**
 * Send emails by using an SMTP server.
 */
final class SmtpMailerConfig implements MailerConfig
{
    public string $transport = SmtpTransport::class;

    public function __construct(
        /**
         * Scheme used for this connection.
         */
        public Scheme $scheme,

        /**
         * Host used for connecting to the SMTP server.
         */
        public string $host,

        /**
         * Port used for connecting to the SMTP server.
         */
        public ?int $port,

        /**
         * Username used for connecting to the SMTP server.
         */
        public ?string $username,

        /**
         * Password used for connecting to the SMTP server.
         */
        public ?string $password,

        /**
         * The default address from which emails will be sent.
         */
        public null|string|Address $from = null,

        /**
         * Whether to use TLS for this connection.
         */
        public bool $automaticTls = true,

        /**
         * Whether to verify the peer certificate.
         */
        public bool $verifyPeer = true,

        /**
         * The domain name to use in the `HELO` command.
         */
        public ?string $localDomain = null,

        /**
         * The fingerprint hash of the peer certificate.
         */
        public ?string $peerFingerprintHash = null,

        /**
         * The minimum number of seconds between two messages required to ping the server.
         */
        public ?int $pingThreshold = null,

        /**
         * The maximum number of messages per second.
         */
        public ?int $maximumMessagesPerSecond = null,

        /**
         * Other options used by the underlying EsmtpTransport.
         */
        public array $options = [],
    ) {}

    public function createTransport(): TransportInterface
    {
        return new EsmtpTransportFactory()->create(new Dsn(
            scheme: $this->scheme->value,
            host: $this->host,
            user: $this->username,
            password: $this->password,
            port: $this->port,
            options: [
                'auto_tls' => $this->automaticTls,
                'verify_peer' => $this->verifyPeer,
                'peer_fingerprint' => $this->peerFingerprintHash,
                'ping_threshold' => $this->pingThreshold,
                'max_per_second' => $this->maximumMessagesPerSecond,
                'local_domain' => $this->localDomain,
                ...$this->options,
            ],
        ));
    }
}
