<?php

namespace Tempest\DateTime\Testing;

use Tempest\Container\Container;
use Tempest\DateTime\DateTime;
use Tempest\DateTime\DateTimeInterface;

final class DateTimeTester
{
    private(set) DateTime $now;

    public function __construct(
        private readonly Container $container,
    ) {}

    /**
     * Instructs Tempest to return the specified `$now` instance when retrieving the current date time.
     */
    public function setNow(DateTime|string $now): void
    {
        if (is_string($now)) {
            $now = DateTime::parse($now);
        }

        $this->now = $now;
        $this->container->register(DateTime::class, fn () => clone $this->now);
        $this->container->register(DateTimeInterface::class, fn () => clone $this->now);
    }
}
