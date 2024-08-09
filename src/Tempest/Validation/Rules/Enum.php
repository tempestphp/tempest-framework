<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\Rule;
use UnexpectedValueException;

#[Attribute]
final readonly class Enum implements Rule
{
    public function __construct(private string $enum)
    {
        if (! enum_exists($this->enum)) {
            throw new UnexpectedValueException(sprintf(
                'The enum parameter must be a valid enum. Was given [%s].',
                $this->enum
            ));
        }
    }

    public function isValid(mixed $value): bool
    {
        if (method_exists($this->enum, 'tryFrom')) {
            return $this->enum::tryFrom($value) !== null;
        }

        return defined("$this->enum::{$value}");
    }

    public function message(): string
    {
        return "The value must be a valid enumeration [$this->enum] case";
    }
}
