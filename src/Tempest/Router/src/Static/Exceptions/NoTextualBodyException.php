<?php

namespace Tempest\Router\Static\Exceptions;

final class NoTextualBodyException extends StaticPageException
{
    public function __construct(
        string $uri,
    ) {
        parent::__construct('No textual body', $uri);
    }
}
