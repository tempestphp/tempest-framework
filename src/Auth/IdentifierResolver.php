<?php

declare(strict_types=1);

namespace Tempest\Auth;

use Tempest\Auth\Exceptions\InvalidLoginException;

interface IdentifierResolver
{
    /**
     * @throws InvalidLoginException
     */
    public function resolve(AuthenticationCall $call): Identifiable;

    public function getIdentifiable(): Identifiable|null;
}
