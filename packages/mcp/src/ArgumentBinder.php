<?php

declare(strict_types=1);

namespace Tempest\Mcp;

use Tempest\Mapper\Casters\BooleanCaster;
use Tempest\Mapper\Casters\EnumCaster;
use Tempest\Mapper\Casters\FloatCaster;
use Tempest\Mapper\Casters\IntegerCaster;
use Tempest\Mcp\Exceptions\ArgumentsFailedValidation;
use Tempest\Mcp\Exceptions\ParametersWereInvalid;
use Tempest\Reflection\MethodReflector;
use Tempest\Reflection\ParameterReflector;
use Tempest\Validation\Rule;
use Tempest\Validation\Rules\IsArray;
use Tempest\Validation\Rules\IsBoolean;
use Tempest\Validation\Rules\IsEnum;
use Tempest\Validation\Rules\IsFloat;
use Tempest\Validation\Rules\IsInteger;
use Tempest\Validation\Rules\IsString;
use Tempest\Validation\Validator;

final readonly class ArgumentBinder
{
    public function __construct(
        private SchemaGenerator $schemaGenerator,
        private Validator $validator,
    ) {}

    /**
     * Validates and casts the given arguments against the schema parameters of the given method, returning named arguments ready to be passed to the container.
     *
     * @return array<string, mixed>
     */
    public function bind(MethodReflector $method, array $arguments): array
    {
        $parameters = $this->schemaGenerator->getSchemaParameters($method);

        foreach (array_keys($arguments) as $name) {
            if (! isset($parameters[$name])) {
                throw ParametersWereInvalid::becauseArgumentWasUnknown((string) $name);
            }
        }

        $values = [];
        $rules = [];

        foreach ($parameters as $name => $parameter) {
            if (! array_key_exists($name, $arguments)) {
                if ($this->schemaGenerator->isRequired($parameter)) {
                    throw ParametersWereInvalid::becauseArgumentWasMissing($name);
                }

                if (! $parameter->hasDefaultValue()) {
                    $values[$name] = null;
                }

                continue;
            }

            $values[$name] = $arguments[$name];
            $rules[$name] = $this->resolveRules($parameter);
        }

        $this->validate($values, $rules);

        $bound = [];

        foreach ($values as $name => $value) {
            $bound[$name] = $this->cast($parameters[$name], $value);
        }

        return $bound;
    }

    private function validate(array $values, array $rules): void
    {
        $failingRules = $this->validator->validateValues($values, $rules);

        if ($failingRules === []) {
            return;
        }

        $failures = [];

        foreach ($failingRules as $field => $failingRulesForField) {
            foreach ($failingRulesForField as $failingRule) {
                $failures[$field][] = $this->validator->getErrorMessage($failingRule, $field);
            }
        }

        throw new ArgumentsFailedValidation($failures);
    }

    /**
     * @return Rule[]
     */
    private function resolveRules(ParameterReflector $parameter): array
    {
        $rules = $parameter->getAttributes(Rule::class);
        $type = $this->schemaGenerator->resolveType($parameter);
        $nullable = $parameter->getType()->isNullable();

        if ($type->isBackedEnum()) {
            $rules[] = new IsEnum($type->asEnum()->getName(), orNull: $nullable);
        } else {
            $rules[] = match (str_replace('?', '', $type->getName())) {
                'string' => new IsString(orNull: $nullable),
                'int' => new IsInteger(orNull: $nullable),
                'float' => new IsFloat(orNull: $nullable),
                'bool' => new IsBoolean(orNull: $nullable),
                'array' => new IsArray(orNull: $nullable),
                default => null,
            };
        }

        return array_filter($rules);
    }

    private function cast(ParameterReflector $parameter, mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        $type = $this->schemaGenerator->resolveType($parameter);
        $nullable = $parameter->getType()->isNullable();

        if ($type->isBackedEnum()) {
            return new EnumCaster($type->asEnum()->getName(), $nullable)->cast($value);
        }

        return match (str_replace('?', '', $type->getName())) {
            'int' => is_int($value) ? $value : new IntegerCaster($nullable)->cast($value),
            'float' => is_float($value) ? $value : new FloatCaster($nullable)->cast($value),
            'bool' => is_bool($value) ? $value : new BooleanCaster($nullable)->cast($value),
            default => $value,
        };
    }
}
