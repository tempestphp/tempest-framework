<?php

declare(strict_types=1);

namespace Tempest\Http\Session;

use Stringable;

final readonly class SessionId implements Stringable
{
    public function __construct(private string $id)
    {
    }

    public function __toString(): string
    {
        return $this->id;
    }
}
