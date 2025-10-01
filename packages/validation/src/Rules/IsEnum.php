<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use BackedEnum;
use Tempest\Validation\HasTranslationVariables;
use Tempest\Validation\Rule;
use UnexpectedValueException;
use UnitEnum;

use function Tempest\Support\arr;

/**
 * Validates that the value is a valid case of a specified enum.
 */
#[Attribute]
final readonly class IsEnum implements Rule, HasTranslationVariables
{
    /**
     * @param class-string<UnitEnum|BackedEnum> $enum
     */
    public function __construct(
        private string $enum,
        private array $only = [],
        private array $except = [],
    ) {
        if (! enum_exists($this->enum)) {
            throw new UnexpectedValueException(sprintf(
                'The enum parameter must be a valid enum. Was given [%s].',
                $this->enum,
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

    public function getTranslationVariables(): array
    {
        $values = arr($this->enum::cases())
            ->filter(fn (UnitEnum $case) => $this->isDesirable($case))
            ->map(static fn (UnitEnum $enum) => $enum instanceof BackedEnum ? $enum->value : $enum->name)
            ->toArray();

        return [
            'enum' => $this->enum,
            'values' => $values,
            'only' => $this->only,
            'except' => $this->except,
        ];
    }

    private function isDesirable(mixed $value): bool
    {
        return match (true) {
            $this->only !== [] => in_array(needle: $value, haystack: $this->only, strict: true),
            $this->except !== [] => ! in_array(needle: $value, haystack: $this->except, strict: true),
            default => true,
        };
    }

    private function retrieveEnumValue(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        if (method_exists($this->enum, 'tryFrom')) {
            return $this->enum::tryFrom($value);
        }

        return defined("{$this->enum}::{$value}") ? $this->enum::{$value} : null;
    }
}
