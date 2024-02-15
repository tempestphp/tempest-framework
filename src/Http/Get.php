<?php

declare(strict_types=1);

namespace Tempest\Http;

use Attribute;

#[Attribute]
final class Get extends Route
{
    public function __construct(string $uri)
    {
        parent::__construct(
            uri: $uri,
            method: Method::GET,
        );
    }
}
