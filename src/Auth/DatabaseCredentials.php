<?php

declare(strict_types=1);

namespace Tempest\Auth;

use Tempest\Auth\Exceptions\InvalidLoginException;
use Tempest\Auth\Exceptions\MissingIdentifiableException;
use Tempest\Database\Query;
use function Tempest\make;

final readonly class DatabaseCredentials implements CredentialsResolver
{
    public function __construct(
        protected AuthConfig $config,
    ) {
    }

    /**
     * @throws InvalidLoginException|MissingIdentifiableException
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

        if (is_null($this->config->identifiable)) {
            throw new MissingIdentifiableException();
        }

        return make($this->config->identifiable)->from($found);
    }
}
