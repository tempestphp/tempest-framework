<?php

namespace Tempest\Router\Static\Exceptions;

final class DeadLinksDetectedException extends StaticPageException
{
    public function __construct(
        string $uri,
        public readonly array $links,
    ) {
        parent::__construct(sprintf('%s has %s dead links', $uri, count($links)), $uri);
    }
}
