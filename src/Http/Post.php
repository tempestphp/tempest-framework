<?php

declare(strict_types=1);

namespace Tempest\Http;

use Attribute;

#[Attribute]
final class Post extends Route
{
    public function __construct(string $uri)
    {
        parent::__construct(
            uri: $uri,
            method: Method::POST,
        );
    }
}
