<?php

declare(strict_types=1);

namespace Tempest\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final readonly class ErrorBag
{
    public function __construct(
        public string $name,
    ) {}
}
