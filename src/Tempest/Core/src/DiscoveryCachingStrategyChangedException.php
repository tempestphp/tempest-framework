<?php

declare(strict_types=1);

namespace Tempest\Core;

use Exception;

final class DiscoveryCachingStrategyChangedException extends Exception
{
    public function __construct(string $previous, mixed $current)
    {
        $current = var_export($current, true);

        $message = sprintf("Discovery caching was changed from `%s` to `%s`. Make sure to run `./tempest discovery:generate` again", $previous, $current);

        parent::__construct($message);
    }
}
