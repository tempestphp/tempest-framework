<?php

declare(strict_types=1);

namespace Tempest\Mcp;

use BackedEnum;
use stdClass;
use Tempest\Mcp\Exceptions\ParameterWasNotSupported;
use Tempest\Reflection\MethodReflector;
use Tempest\Reflection\ParameterReflector;
use Tempest\Reflection\TypeReflector;
use Tempest\Validation\HasTranslationVariables;
use Tempest\Validation\Rule;
use Tempest\Validation\Rules\HasLength;
use Tempest\Validation\Rules\IsBetween;
use Tempest\Validation\Rules\IsEmail;
use Tempest\Validation\Rules\IsIn;
use Tempest\Validation\Rules\IsNotEmptyString;
use Tempest\Validation\Rules\IsUrl;
use Tempest\Validation\Rules\IsUuid;

final readonly class SchemaGenerator
{
    /**
     * Returns the method parameters that are part of the input schema. Other parameters are resolved through the container.
     *
     * @return array<string, ParameterReflector>
     */
    public function getSchemaParameters(MethodReflector $method): array
    {
        $parameters = [];

        foreach ($method->getParameters() as $parameter) {
            if (! $this->resolveType($parameter) instanceof TypeReflector) {
                continue;
            }

            $parameters[$parameter->getName()] = $parameter;
        }

        return $parameters;
    }

    /**
     * Asserts that every parameter of the given handler can either be represented in a schema or be resolved
     * through the container, so unsupported signatures fail at discovery time instead of at call time.
     */
    public function assertSupported(MethodReflector $method): void
    {
        foreach ($method->getParameters() as $parameter) {
            if ($parameter->isVariadic()) {
                throw ParameterWasNotSupported::becauseItWasVariadic($method, $parameter->getName());
            }

            $type = $parameter->getType();

            if (! $type->isUnion()) {
                continue;
            }

            $members = array_filter($type->split(), static fn (TypeReflector $member) => $member->getName() !== 'null');

            if (count($members) > 1) {
                throw ParameterWasNotSupported::becauseUnionTypesAreNotSupported($method, $parameter->getName());
            }
        }
    }

    /**
     * Creates the JSON schema describing the input of a tool handler.
     */
    public function createInputSchema(MethodReflector $method): array
    {
        $properties = [];
        $required = [];

        foreach ($this->getSchemaParameters($method) as $name => $parameter) {
            $properties[$name] = $this->createParameterSchema($parameter);

            if ($this->isRequired($parameter)) {
                $required[] = $name;
            }
        }

        return [
            'type' => 'object',
            'properties' => $properties === [] ? new stdClass() : $properties,
            ...($required === [] ? [] : ['required' => $required]),
        ];
    }

    /**
     * Creates the MCP argument definitions describing the input of a prompt handler.
     */
    public function createPromptArguments(MethodReflector $method): array
    {
        $arguments = [];

        foreach ($this->getSchemaParameters($method) as $name => $parameter) {
            $description = $parameter->getAttribute(Description::class)?->description;

            $arguments[] = [
                'name' => $name,
                ...($description === null ? [] : ['description' => $description]),
                'required' => $this->isRequired($parameter),
            ];
        }

        return $arguments;
    }

    public function isRequired(ParameterReflector $parameter): bool
    {
        return ! $parameter->hasDefaultValue() && ! $parameter->getType()->isNullable();
    }

    /**
     * Resolves the schema type of a parameter, or `null` when the parameter is not part of the schema.
     */
    public function resolveType(ParameterReflector $parameter): ?TypeReflector
    {
        $type = $parameter->getType();

        if ($type->isUnion()) {
            $members = array_values(array_filter(
                $type->split(),
                static fn (TypeReflector $member) => $member->getName() !== 'null',
            ));

            if (count($members) !== 1) {
                return null;
            }

            $type = $members[0];
        }

        if ($type->isScalar() || $type->isBackedEnum() || $type->getName() === 'array' || $type->getName() === '?array') {
            return $type;
        }

        return null;
    }

    private function createParameterSchema(ParameterReflector $parameter): array
    {
        $type = $this->resolveType($parameter);
        $nullable = $parameter->getType()->isNullable();

        if ($type->isBackedEnum()) {
            $enum = $type->asEnum();
            $jsonType = $enum->getBackingType()?->getName() === 'int' ? 'integer' : 'string';

            $values = array_column($enum->getCases(), 'value');

            $schema = [
                'type' => $nullable ? [$jsonType, 'null'] : $jsonType,
                'enum' => $nullable ? [...$values, null] : $values,
            ];
        } else {
            $jsonType = match (str_replace('?', '', $type->getName())) {
                'int' => 'integer',
                'float' => 'number',
                'bool' => 'boolean',
                'array' => 'array',
                default => 'string',
            };

            $schema = [
                'type' => $nullable ? [$jsonType, 'null'] : $jsonType,
            ];
        }

        if (($description = $parameter->getAttribute(Description::class)) instanceof Description) {
            $schema['description'] = $description->description;
        }

        foreach ($parameter->getAttributes(Rule::class) as $rule) {
            $schema = [...$schema, ...$this->createConstraints($rule)];
        }

        if ($parameter->hasDefaultValue()) {
            $default = $parameter->getDefaultValue();

            $schema['default'] = $default instanceof BackedEnum ? $default->value : $default;
        }

        return $schema;
    }

    private function createConstraints(Rule $rule): array
    {
        $variables = $rule instanceof HasTranslationVariables ? $rule->getTranslationVariables() : [];

        return match ($rule::class) {
            HasLength::class => [
                ...($variables['min'] === null ? [] : ['minLength' => $variables['min']]),
                ...($variables['max'] === null ? [] : ['maxLength' => $variables['max']]),
            ],
            IsBetween::class => [
                'minimum' => $variables['min'],
                'maximum' => $variables['max'],
            ],
            IsIn::class => $variables['not'] ? [] : ['enum' => $variables['values']],
            IsNotEmptyString::class => ['minLength' => 1],
            IsEmail::class => ['format' => 'email'],
            IsUrl::class => ['format' => 'uri'],
            IsUuid::class => ['format' => 'uuid'],
            default => [],
        };
    }
}
