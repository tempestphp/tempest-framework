<?php

namespace Tempest\Database\Builder;

use ReflectionException;
use Tempest\Database\Config\DatabaseConfig;
use Tempest\Database\Table;
use Tempest\Reflection\ClassReflector;
use Tempest\Validation\Exceptions\ValidationException;
use Tempest\Validation\SkipValidation;
use Tempest\Validation\Validator;

use function Tempest\get;

final class ModelInspector
{
    private ?ClassReflector $modelClass;

    public function __construct(
        private object|string $model,
    ) {
        if ($this->model instanceof ClassReflector) {
            $this->modelClass = $this->model;
        } else {
            try {
                $this->modelClass = new ClassReflector($this->model);
            } catch (ReflectionException) {
                $this->modelClass = null;
            }
        }
    }

    public function isObjectModel(): bool
    {
        return $this->modelClass !== null;
    }

    public function getTableDefinition(): TableDefinition
    {
        if (! $this->isObjectModel()) {
            return new TableDefinition($this->model);
        }

        $specificName = $this->modelClass
            ->getAttribute(Table::class)
            ?->name;

        $conventionalName = get(DatabaseConfig::class)
            ->namingStrategy
            ->getName($this->modelClass->getName());

        return new TableDefinition($specificName ?? $conventionalName);
    }

    public function getPropertyValues(): array
    {
        if (! $this->isObjectModel()) {
            return [];
        }

        if (! is_object($this->model)) {
            return [];
        }

        $values = [];

        foreach ($this->modelClass->getProperties() as $property) {
            if (is_object($this->model) && ! $property->isInitialized($this->model)) {
                continue;
            }

            if ($property->getIterableType()?->isRelation()) {
                continue;
            }

            $name = $property->getName();

            $values[$name] = $property->getValue($this->model);
        }

        return $values;
    }

    public function validate(mixed ...$data): void
    {
        if (! $this->isObjectModel()) {
            return;
        }

        $validator = new Validator();
        $failingRules = [];

        foreach ($data as $key => $value) {
            $property = $this->modelClass->getProperty($key);

            if ($property->hasAttribute(SkipValidation::class)) {
                continue;
            }

            $failingRulesForProperty = $validator->validateValueForProperty(
                $property,
                $value,
            );

            if ($failingRulesForProperty !== []) {
                $failingRules[$key] = $failingRulesForProperty;
            }
        }

        if ($failingRules !== []) {
            throw new ValidationException($this->modelClass->getName(), $failingRules);
        }
    }
}
