<?php

namespace Tempest\Router\Static\Exceptions;

final class NoTextualBodyGenerationFailed extends StaticPageGenerationFailed
{
    public function __construct(
        string $uri,
    ) {
        parent::__construct('No textual body', $uri);
    }
}
