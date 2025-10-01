<?php

use function Tempest\uri;

final class UriNamespaceChange
{
    public function __invoke()
    {
        return uri(self::class);
    }
}