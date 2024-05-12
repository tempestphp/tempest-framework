<?php

declare(strict_types=1);

namespace Tempest\Auth\Authenticators;

use Tempest\Auth\Contracts\Authenticable;
use Tempest\Auth\Exceptions\InvalidLoginException;
use Tempest\Database\Query;

final class DatabaseAuthenticator extends GenericAuthenticator
{
    public function login(Authenticable $authenticable): void
    {
        if (! $this->authenticableExists($authenticable)) {
            throw new InvalidLoginException();
        }

        // TODO: Finish implementation
    }

    public function logout(): void
    {
        // TODO: Implement logout() method.
    }

    public function user(): ?Authenticable
    {
        // TODO: Implement user() method.
    }

    private function authenticableExists(Authenticable $authenticable): bool
    {
        $query = new Query(
            "SELECT * FROM :table
            WHERE :identifier_field = :identifier_value
            AND :secret_field = :secret_value LIMIT 1",
            [
                'table' => $authenticable->source(),
                'identifier_field' => $authenticable->identifier(),
                'identifier_value' => $authenticable->identifierValue(),
                'secret_field' => $authenticable->secret(),
                'secret_value' => $authenticable->secretValue(),
            ]
        );

        return ! is_null($query->fetchFirst());
    }
}
