<?php

namespace Tempest\Router\Static\Exceptions;

use Exception;

final class NoTextualBodyException extends Exception implements StaticPageException
{
    public function __construct(
        public readonly string $uri,
    ) {
        parent::__construct('No textual body');
    }
}
