<?php

declare(strict_types=1);

namespace Tempest\Mcp;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
final readonly class Description
{
    public function __construct(
        public string $description,
    ) {}
}
