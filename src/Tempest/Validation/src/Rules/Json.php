<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\Rule;

#[Attribute]
final readonly class Json implements Rule
{
    public function __construct(
        private ?int $depth = null,
        private ?int $flags = null,
    ) {
    }

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

    public function message(): string
    {
        return 'Value should be a valid JSON string';
    }
}
