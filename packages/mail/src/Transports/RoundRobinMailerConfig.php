<?php

namespace Tempest\Mail\Transports;

use Symfony\Component\Mailer\Transport\RoundRobinTransport;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Tempest\DateTime\Duration;
use Tempest\Mail\MailerConfig;
use Tempest\Support\Arr;

/**
 * Send emails using the round-robin balancing strategy.
 */
final class RoundRobinMailerConfig implements MailerConfig
{
    public string $transport = RoundRobinTransport::class;

    public function __construct(
        /**
         * List of configurations to use.
         *
         * @var MailerConfig[]
         */
        public array $transports,

        /**
         * The period in seconds to wait before retrying a failed transport.
         */
        public int|Duration $waitTimeBeforeRetrying = 60,
    ) {}

    public function createTransport(): TransportInterface
    {
        return new RoundRobinTransport(
            transports: $this->buildTransports(),
            retryPeriod: $this->waitTimeBeforeRetrying instanceof Duration
                ? $this->waitTimeBeforeRetrying->getTotalSeconds()
                : $this->waitTimeBeforeRetrying,
        );
    }

    /** @return TransportInterface[] */
    private function buildTransports(): array
    {
        return Arr\map($this->transports, fn (MailerConfig $config) => $config->createTransport());
    }
}
