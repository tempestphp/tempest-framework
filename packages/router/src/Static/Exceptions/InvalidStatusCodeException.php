<?php

namespace Tempest\Router\Static\Exceptions;

use Exception;
use Tempest\Http\Status;

final class InvalidStatusCodeException extends Exception implements StaticPageException
{
    public function __construct(
        public readonly string $uri,
        public readonly Status $status,
    ) {
        parent::__construct("HTTP {$status->value}");
    }
}
