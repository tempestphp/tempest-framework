<?php

declare(strict_types=1);

namespace Tempest\View;

use JsonSerializable;

final readonly class ViewComponentParameter
{
    public function __construct(
        public string $name,
        public bool $required = false,
        public ?string $description = null,
        public mixed $default = null,
        public ?array $possibleValues = null,
    ) {}
}
