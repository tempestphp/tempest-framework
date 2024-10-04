<?php

declare(strict_types=1);

namespace Tempest\Reflection;

use Exception;
use Generator;

final readonly class TypeValidator
{
    private const array BUILTIN_VALIDATION = [
        'array' => 'is_array',
        'bool' => 'is_bool',
        'callable' => 'is_callable',
        'float' => 'is_float',
        'int' => 'is_int',
        'null' => 'is_null',
        'object' => 'is_object',
        'resource' => 'is_resource',
        'string' => 'is_string',
        // these are handled explicitly
        'false' => null,
        'mixed' => null,
        'never' => null,
        'true' => null,
        'void' => null,
    ];

    public function accepts(string $definition, mixed $input, int $depth = 0): bool
    {
        if ($this->isNullable($definition) && $input === null) {
            return true;
        }

        if ($this->isBuiltIn($definition)) {
            return match ($definition) {
                'false' => $input === false,
                'mixed' => true,
                'never' => false,
                'true' => $input === true,
                'void' => false,
                default => self::BUILTIN_VALIDATION[$definition]($input),
            };
        }

        if ($this->isClass($definition)) {
            if (is_string($input)) {
                return $this->matches($definition, $input);
            }

            $definition = $this->cleanDefinition($definition);

            return $input instanceof $definition;
        }

        if ($this->isIterable($definition)) {
            return is_iterable($input);
        }

        if ($depth > 0) {
            throw new Exception("Max recursive depth exceeded.");
        }

        if (str_contains($definition, '|')) {
            foreach ($this->split($definition) as $type) {
                try {
                    if ($this->accepts($type, $input, $depth + 1)) {
                        return true;
                    }
                } catch (Exception) {
                }
            }

            return false;
        }

        if (str_contains($definition, '&')) {
            foreach ($this->split($definition) as $type) {
                try {
                    if (! $this->accepts($type, $input, $depth + 1)) {
                        return false;
                    }
                } catch (Exception) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    public function matches(string $definition, string $className): bool
    {
        return is_a($this->cleanDefinition($definition), $className, true);
    }

    public function isBuiltIn(string $definition): bool
    {
        return isset(self::BUILTIN_VALIDATION[$this->cleanDefinition($definition)]);
    }

    public function isClass(string $definition): bool
    {
        return class_exists($this->cleanDefinition($definition));
    }

    public function isIterable(string $definition): bool
    {
        return in_array($this->cleanDefinition($definition), [
            'array',
            'iterable',
            Generator::class,
        ]);
    }

    public function isNullable(string $definition): bool
    {
        return str_contains($definition, '?')
            || str_contains($definition, 'null');
    }

    public function split(string $definition): array
    {
        return preg_split('/[&|]/', $definition);
    }

    private function cleanDefinition(string $definition): string
    {
        return ltrim($definition, '?');
    }
}
