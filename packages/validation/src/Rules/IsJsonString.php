<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\Rule;
use ValueError;

use const JSON_INVALID_UTF8_IGNORE;

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
        if (! is_string($value)) {
            return false;
        }

        $arguments = ['json' => $value];

        if ($this->depth !== null) {
            $arguments['depth'] = $this->depth;
        }

        if ($this->flags !== null) {
            if ($this->flags !== JSON_INVALID_UTF8_IGNORE) {
                throw new ValueError('Invalid JSON validation flags provided.');
            }

            $arguments['flags'] = $this->flags;
        }

        return json_validate(...$arguments);
    }
}
