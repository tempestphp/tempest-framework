<?php

declare(strict_types=1);

namespace Tempest\Auth\Authenticators;

use Tempest\Auth\Contracts\Identifiable;
use Tempest\Auth\Exceptions\InvalidLoginException;
use Tempest\Database\Query;

final class DatabaseAuthenticator extends GenericAuthenticator
{
    public function login(Identifiable $identifiable): void
    {
        if (! $this->identifiableExists($identifiable)) {
            throw new InvalidLoginException();
        }

        // TODO: Finish implementation
    }

    public function logout(): void
    {
        // TODO: Implement logout() method.
    }

    public function user(): ?Identifiable
    {
        // TODO: Implement user() method.
    }

    private function identifiableExists(Identifiable $identifiable): bool
    {
        $query = new Query(
            "SELECT * FROM :table
            WHERE :identifier_field = :identifier_value
            AND :secret_field = :secret_value LIMIT 1",
            [
                'table' => $identifiable->source(),
                'identifier_field' => $identifiable->identifier(),
                'identifier_value' => $identifiable->identifierValue(),
                'secret_field' => $identifiable->secret(),
                'secret_value' => $identifiable->secretValue(),
            ]
        );

        return ! is_null($query->fetchFirst());
    }
}
