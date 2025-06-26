<?php

namespace Tempest\Router\Static\Exceptions;

use Exception;

final class DeadLinksDetectedException extends Exception implements StaticPageException
{
    public function __construct(
        public readonly string $uri,
        public readonly array $links,
    ) {
        parent::__construct(sprintf('%s has %s dead links', $uri, count($links)));
    }
}
