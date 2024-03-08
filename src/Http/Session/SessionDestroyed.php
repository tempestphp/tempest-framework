<?php

namespace Tempest\Http\Session;

final readonly class SessionDestroyed
{
    public function __construct(
        public SessionId $id,
    ) {}
}