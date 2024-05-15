<?php

declare(strict_types=1);

namespace Tempest\Auth;

use Tempest\Database\IsModel;

trait HasIdentity
{
    use IsModel;

    protected const string IDENTIFIER = 'email';

    protected const string SECRET = 'password';

    public function source(): string
    {
        return (string) static::table();
    }

    public function identifier(): string
    {
        return static::IDENTIFIER;
    }

    public function identifierValue(): string
    {
        return $this->{$this->identifier()};
    }

    public function secret(): string
    {
        return static::SECRET;
    }

    public function secretValue(): string
    {
        return $this->{$this->secret()};
    }
}
