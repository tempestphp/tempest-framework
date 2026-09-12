<?php

declare(strict_types=1);

namespace Tempest\Http\Session;

use Exception;

final class InvalidSessionId extends Exception
{
    public function __construct(string $id)
    {
        parent::__construct(
            sprintf('The session identifier `%s` is not a valid session id.', $id),
        );
    }
}
