<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\Rule;
use UnexpectedValueException;
use UnitEnum;

#[Attribute]
final readonly class Enum implements Rule
{
    public function __construct(private string $enum, private array $only = [], private array $except = [])
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
        if ($value instanceof $this->enum) {
            return $this->isDesirable($value);
        }

        return ($enumValue = $this->retrieveEnumValue($value)) !== null && $this->isDesirable($enumValue);
    }

    /**
     * Specify the cases that should be considered valid.
     *
     * @param UnitEnum|array<UnitEnum> $values
     */
    public function only(UnitEnum|array $values): self
    {
        return new self(
            enum: $this->enum,
            only: [
                ...$this->only,
                ...(is_array($values) ? $values : func_get_args()),
            ],
        );
    }

    /**
     * Specify the cases that should be considered invalid.
     *
     * @param UnitEnum|array<UnitEnum> $values
     */
    public function except(UnitEnum|array $values): self
    {
        return new self(
            enum: $this->enum,
            except: [
                ...$this->except,
                ...(is_array($values) ? $values : func_get_args()),
            ],
        );
    }

    public function message(): string
    {
        return "The value must be a valid enumeration [$this->enum] case";
    }

    private function isDesirable($value): bool
    {
        return match (true) {
            ! empty($this->only) => in_array(needle: $value, haystack: $this->only, strict: true),
            ! empty($this->except) => ! in_array(needle: $value, haystack: $this->except, strict: true),
            default => true,
        };
    }

    private function retrieveEnumValue(mixed $value)
    {
        if (method_exists($this->enum, 'tryFrom')) {
            return $this->enum::tryFrom($value);
        }

        return defined("$this->enum::{$value}") ? $this->enum::{$value} : null;
    }
}
