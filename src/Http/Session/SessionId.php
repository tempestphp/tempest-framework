<?php

namespace Tempest\Http\Session;

final readonly class SessionId
{
    public function __construct(private string $id) {}

    public function __toString(): string
    {
        return $this->id;
    }
}