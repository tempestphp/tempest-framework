<?php

namespace Tempest\Router\Static\Exceptions;

use Exception;

abstract class StaticPageException extends Exception
{
    public function __construct(
        string $message,
        public readonly string $uri,
    ) {
        parent::__construct($message);
    }
}
