<?php

namespace Tempest\HttpApi;

use Attribute;

#[Attribute]
final class ResourceRecord
{
    /**
     * @param class-string $resourceRecord
     */
    public function __construct(
        public string $resourceRecord,
    ) {}
}
