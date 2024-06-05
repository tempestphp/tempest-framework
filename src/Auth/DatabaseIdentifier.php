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

        $found = (new Query(
            "SELECT * FROM :table WHERE :identifier_field = :identifier_value LIMIT 1",
            [
                'table' => $this->config->databaseSource['source'],
                'identifier_field' => $this->config->databaseSource['identifier'],
                'identifier_value' => $call->identifier,
            ]
        ))->fetchFirst();

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

        $query = new Query(
            "SELECT * FROM :table WHERE :identifier_field = :identifier_value LIMIT 1",
            [
                'table' => $this->config->databaseSource['source'],
                'identifier_field' => $sessionInfo['identifier_field'],
                'identifier_value' => $sessionInfo['identifier_value'],
            ]
        );

        return make($this->config->identifiable)->from($query->fetchFirst());
    }
}
