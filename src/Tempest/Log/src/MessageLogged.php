<?php

declare(strict_types=1);

namespace Tempest\Log;

use Stringable;

final class MessageLogged
{
    public function __construct(
        public LogLevel $level,
        public Stringable|string $message,
        public array $context = [],
    ) {
    }
}
