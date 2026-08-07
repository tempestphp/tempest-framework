<?php

declare(strict_types=1);

namespace Tempest\Support\Ip;

use Exception;

final class InvalidIpAddress extends Exception
{
    public function __construct(string $ip)
    {
        parent::__construct(sprintf('The value `%s` is not a valid IP address.', $ip));
    }
}
