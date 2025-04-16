<?php

namespace Tempest\DateTime;

use Tempest\Container\Container;
use Tempest\Container\Initializer;

final class DateTimeInitializer implements Initializer
{
    public function initialize(Container $container): DateTimeInterface
    {
        return DateTime::fromTimestamp(Timestamp::now());
    }
}
