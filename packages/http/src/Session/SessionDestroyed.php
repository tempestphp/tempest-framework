<?php

declare(strict_types=1);

namespace Tempest\Http\Session;

final readonly class SessionDestroyed
{
    public function __construct(
        public SessionId $id,
    ) {}
}
