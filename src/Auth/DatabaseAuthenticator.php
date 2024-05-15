<?php

declare(strict_types=1);

namespace Tempest\Auth;

use Tempest\Auth\Exceptions\InvalidLoginException;
use Tempest\Database\Query;

final class DatabaseAuthenticator extends GenericAuthenticator
{
    /**
     * @throws InvalidLoginException
     */
    public function login(Identifiable $identifiable): void
    {
        if (! $this->identifiableExists($identifiable)) {
            throw new InvalidLoginException();
        }

        $this->createSession($identifiable);
    }

    /**
     * @return Identifiable|null
     */
    public function user()
    {
        $sessionUser = $this->session->get(self::SESSION_USER_KEY);
        if (is_null($sessionUser)) {
            return null;
        }

        $query = new Query(
            "SELECT * FROM :table WHERE :identifier_field = :identifier_value LIMIT 1",
            [
                'table' => $sessionUser['source'],
                'identifier_field' => $sessionUser['identifier_field'],
                'identifier_value' => $sessionUser['identifier_value'],
            ]
        );

        return $query->fetchFirst();
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
