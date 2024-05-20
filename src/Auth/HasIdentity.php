<?php

declare(strict_types=1);

namespace Tempest\Auth;

trait HasIdentity
{
    public function source(): string
    {
        return (string) static::table();
    }

    public function identifierField(): string
    {
        return 'email';
    }

    public function identifierValue(): string
    {
        return $this->{$this->identifierField()};
    }

    public function secretField(): string
    {
        return 'password';
    }

    public function secretValue(): string
    {
        return $this->{$this->secretField()};
    }

    public function setSecret(string $secret): static
    {
        $this->{$this->secretField()} = password_hash($secret, PASSWORD_DEFAULT);

        return $this;
    }

    public function setCredentials(string $identifier, string $secret): static
    {
        $this->{$this->identifierField()} = $identifier;

        return $this->setSecret($secret);
    }
}
