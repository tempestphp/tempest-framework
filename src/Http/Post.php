<?php

namespace Tempest\Http;

use Attribute;

#[Attribute]
final readonly class Post extends Route
{
    public function __construct(string $uri)
    {
        parent::__construct(
            uri: $uri,
            method: Method::POST,
        );
    }
}