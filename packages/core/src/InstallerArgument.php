<?php

declare(strict_types=1);

namespace Tempest\Core;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
final readonly class InstallerArgument
{
    public function __construct(
        public ?string $name = null,
        public ?string $prompt = null,
    ) {}
}
