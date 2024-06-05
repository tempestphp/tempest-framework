<?php

declare(strict_types=1);

namespace Tempest\Auth;

use Tempest\Auth\Exceptions\InvalidLoginException;
use Tempest\Database\Query;
use function Tempest\make;

final readonly class DatabaseIdentifier implements IdentifierResolver
{
    public function __construct(
        protected AuthConfig $config,
        protected Authenticator $authenticator,
    ) {
    }

    /**
     * @throws InvalidLoginException
     */
    public function resolve(AuthenticationCall $call): Identifiable
    {
        if (! $call instanceof DatabaseAuthenticationCall) {
            throw new InvalidLoginException();
        }

        $found = $this->fetchIdentifiable(
            table: $this->config->databaseSource['source'],
            identifierField: $this->config->databaseSource['identifier'],
            identifierValue: $call->identifier,
        );

        if (is_null($found)) {
            throw new InvalidLoginException();
        }

        if (! password_verify($call->password, $found[$this->config->databaseSource['password']])) {
            throw new InvalidLoginException();
        }

        return make($this->config->identifiable)->from($found);
    }

    public function getIdentifiable(): Identifiable|null
    {
        $sessionInfo = $this->authenticator->getSessionInfo();
        if (is_null($sessionInfo)) {
            return null;
        }

        $identifiable = $this->fetchIdentifiable(
            table: $this->config->databaseSource['source'],
            identifierField: $sessionInfo['identifier_field'],
            identifierValue: $sessionInfo['identifier_value'],
        );

        return is_null($identifiable) ? null : make($this->config->identifiable)->from($identifiable);
    }

    private function fetchIdentifiable(string $table, string $identifierField, string $identifierValue): array|null
    {
        return (new Query(
            "SELECT * FROM :table WHERE :identifier_field = :identifier_value LIMIT 1",
            [
                'table' => $table,
                'identifier_field' => $identifierField,
                'identifier_value' => $identifierValue,
            ]
        ))->fetchFirst();
    }
}
