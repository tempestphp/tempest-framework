<?php

declare(strict_types=1);

namespace Tempest\Http\Session;

final readonly class SessionConfig
{
    public function __construct(
        public string $path = __DIR__ . '/sessions',
    ) {
    }
}
