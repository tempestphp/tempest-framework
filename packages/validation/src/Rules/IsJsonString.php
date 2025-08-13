<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\Rule;

/**
 * Validates that the value is a valid JSON string.
 */
#[Attribute]
final readonly class IsJsonString implements Rule
{
    public function __construct(
        private ?int $depth = null,
        private ?int $flags = null,
    ) {}

    public function isValid(mixed $value): bool
    {
        $arguments = ['json' => $value];

        if ($this->depth !== null) {
            $arguments['depth'] = $this->depth;
        }

        if ($this->flags !== null) {
            $arguments['flags'] = $this->flags;
        }

        return json_validate(...$arguments);
    }
}
