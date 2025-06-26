<?php

declare(strict_types=1);

namespace Tempest\CommandBus\Exceptions;

use Exception;

final class PendingCommandCouldNotBeResolved extends Exception
{
    public function __construct(
        public string $uuid,
    ) {
        parent::__construct("Could not resolve command [{$uuid}].");
    }
}
