<?php

declare(strict_types=1);

namespace Tempest\Validation;

use Closure;
use Tempest\Container\Singleton;
use Tempest\Intl\Translator;
use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\PropertyReflector;
use Tempest\Support\Arr;
use Tempest\Validation\Exceptions\ValidationFailed;
use Tempest\Validation\Rules\IsBoolean;
use Tempest\Validation\Rules\IsEnum;
use Tempest\Validation\Rules\IsFloat;
use Tempest\Validation\Rules\IsInteger;
use Tempest\Validation\Rules\IsNotNull;
use Tempest\Validation\Rules\IsString;

use function Tempest\Support\arr;
use function Tempest\Support\str;

#[Singleton]
final readonly class Validator
{
    public function __construct(
        private Translator $translator,
    ) {}

    /**
     * Validates the values of public properties on the specified object using attribute rules.
     */
    public function validateObject(object $object): void
    {
        $class = new ClassReflector($object);

        $failingRules = [];

        foreach ($class->getPublicProperties() as $property) {
            if (! $property->isInitialized($object)) {
                continue;
            }

            $value = $property->getValue($object);

            $failingRules[$property->getName()] = $this->validateValueForProperty($property, $value);
        }

        if ($failingRules !== []) {
            throw $this->createValidationFailureException($failingRules, $object);
        }
    }

    /**
     * Creates a {@see ValidationFailed} exception from the given rule failures, populated with error messages.
     *
     * @param array<string,Rule[]> $failingRules
     */
    public function createValidationFailureException(array $failingRules, null|object|string $subject = null): ValidationFailed
    {
        return new ValidationFailed($failingRules, $subject, Arr\map_iterable($failingRules, function (array $rules, string $field) {
            return Arr\map_iterable($rules, fn (Rule $rule) => $this->getErrorMessage($rule, $field));
        }));
    }

    /**
     * Validates the specified `$values` for the corresponding public properties on the specified `$class`, using built-in PHP types and attribute rules.
     *
     * @param ClassReflector|class-string $class
     * @return Rule[]
     */
    public function validateValuesForClass(ClassReflector|string $class, ?array $values, string $prefix = ''): array
    {
        $class = is_string($class) ? new ClassReflector($class) : $class;

        $failingRules = [];

        $values = arr($values)->undot();

        foreach ($class->getPublicProperties() as $property) {
            if ($property->hasAttribute(SkipValidation::class)) {
                continue;
            }

            $key = $prefix . $property->getName();

            if (! $values->hasKey($key) && $property->hasDefaultValue()) {
                continue;
            }

            $value = $values->get($key);

            $failingRulesForProperty = $this->validateValueForProperty($property, $value);

            if ($failingRulesForProperty !== []) {
                $failingRules[$key] = $failingRulesForProperty;
            }

            if ($property->isNullable() && $value === null) {
                continue;
            }

            if ($property->getType()->isClass() && ! $property->getType()->isEnum()) {
                $failingRules = [
                    ...$failingRules,
                    ...$this->validateValuesForClass(
                        class: $property->getType()->asClass(),
                        values: $values->dot()->toArray(),
                        prefix: $key . '.',
                    ),
                ];
            }
        }

        return $failingRules;
    }

    /**
     * Validates `$value` against the specified `$property`, using built-in PHP types and attribute rules.
     *
     * @return Rule[]
     */
    public function validateValueForProperty(PropertyReflector $property, mixed $value): array
    {
        $rules = $property->getAttributes(Rule::class);

        if ($property->getType()->isScalar()) {
            $rules[] = match ($property->getType()->getName()) {
                'string' => new IsString(orNull: $property->isNullable()),
                'int' => new IsInteger(orNull: $property->isNullable()),
                'float' => new IsFloat(orNull: $property->isNullable()),
                'bool' => new IsBoolean(orNull: $property->isNullable()),
                default => null,
            };
        } elseif (! $property->isNullable()) {
            // We only add the NotNull rule if we're not dealing with scalar types, since the null check is included in the scalar rules
            $rules[] = new IsNotNull();
        }

        if ($property->getType()->isEnum()) {
            $rules[] = new IsEnum($property->getType()->getName());
        }

        return $this->validateValue($value, $rules);
    }

    /**
     * Validates the specified `$value` against the specified set of `$rules`. If a rule is a closure, it may return a string as a validation error.
     *
     * @param Rule|array<Rule|(Closure(mixed $value):string|false)>|(Closure(mixed $value):string|false) $rules
     * @return Rule[]
     */
    public function validateValue(mixed $value, Closure|Rule|array $rules): array
    {
        $failingRules = [];

        foreach (Arr\wrap($rules) as $rule) {
            if (! $rule) {
                continue;
            }

            $rule = $this->convertToRule($rule, $value);

            if (! $rule->isValid($value)) {
                $failingRules[] = $rule;
            }
        }

        return $failingRules;
    }

    /**
     * Validates the specified `$values` against the specified set `$rules`.
     * The `$rules` array is expected to have the same keys as `$values`, associated with instance of {@see Tempest\Validation\Rule}.
     * If `$rules` doesn't contain a key for a value, it will not be validated.
     *
     * @param array<string,mixed> $values
     * @param array<string,Rule|(Closure(mixed $value):string|false)> $rules
     *
     * @return Rule[]
     */
    public function validateValues(iterable $values, array $rules): array
    {
        $failingRules = [];

        foreach ($values as $key => $value) {
            if (! array_key_exists($key, $rules)) {
                continue;
            }

            if ($failures = $this->validateValue($value, $rules[$key])) {
                $failingRules[$key] = $failures;
            }
        }

        return $failingRules;
    }

    /**
     * Gets a localized validation error message for the specified rule.
     */
    public function getErrorMessage(Rule $rule, ?string $field = null): string
    {
        if ($rule instanceof HasErrorMessage) {
            return $rule->getErrorMessage();
        }

        $ruleTranslationKey = str($rule::class)
            ->classBasename()
            ->snake()
            ->replaceEvery([ // those are snake case issues that we manually fix for consistency
                'i_pv6' => 'ipv6',
                'i_pv4' => 'ipv4',
                'reg_ex' => 'regex',
            ])
            ->toString();

        $variables = [
            'field' => $this->getFieldName($ruleTranslationKey, $field),
        ];

        if ($rule instanceof HasTranslationVariables) {
            $variables = [...$variables, ...$rule->getTranslationVariables()];
        }

        return $this->translator->translate("validation_error.{$ruleTranslationKey}", ...$variables);
    }

    private function getFieldName(string $key, ?string $field = null): string
    {
        $translatedField = $this->translator->translate("validation_field.{$key}");

        if ($translatedField === "validation_field.{$key}") {
            return $field ?? 'Value';
        }

        return $field ?? $translatedField;
    }

    private function convertToRule(Rule|Closure $rule, mixed $value): Rule
    {
        if ($rule instanceof Rule) {
            return $rule;
        }

        $result = $rule($value);

        [$isValid, $message] = match (true) {
            is_string($result) => [false, $result],
            $result === false => [false, 'Value did not pass validation.'],
            default => [true, ''],
        };

        return new class($isValid, $message) implements Rule, HasErrorMessage {
            public function __construct(
                private bool $isValid,
                public string $message,
            ) {}

            public function isValid(mixed $value): bool
            {
                return $this->isValid;
            }

            public function getErrorMessage(): string
            {
                return $this->message;
            }
        };
    }
}
