<?php

namespace Tempest\Router\Static\Exceptions;

use Tempest\Http\Status;

final class InvalidStatusCodeException extends StaticPageException
{
    public function __construct(
        string $uri,
        public readonly Status $status,
    ) {
        parent::__construct("HTTP {$status->value}", $uri);
    }
}
