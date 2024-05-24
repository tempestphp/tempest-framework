<?php

declare(strict_types=1);

namespace Tempest\Auth;

trait HasIdentity
{
    public function identifierField(): string
    {
        return 'id';
    }

    public function identifierValue(): int|string
    {
        return $this->{$this->identifierField()};
    }
}
