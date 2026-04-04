<?php

declare(strict_types=1);

namespace Tempest\ClassVariance;

use InvalidArgumentException;

/**
 * Core variant resolution engine, shared by all ClassVariance implementations.
 *
 * The class using this trait must declare the following public readable properties:
 *   - array|string $base
 *   - ClassMerger  $merger
 *   - array        $variants
 *   - array        $compoundVariants
 *   - array        $defaultVariants
 *
 * @property-read array|string $base
 * @property-read ClassMerger $merger
 * @property-read array $variants
 * @property-read array $compoundVariants
 * @property-read array $defaultVariants
 *
 * @internal
 */
trait ResolvesVariants
{
    /** @param array<string, string|bool|array<string, string|bool>> $props */
    public function __invoke(array $props = [], string $slot = ''): string
    {
        $props = $this->applyDefaultVariants($props);
        $slot = $this->resolveSlot($slot);

        $classes = ClassNames::of($this->base, $slot)
            ->concat($this->resolveVariants($props, $slot))
            ->concat($this->resolveCompoundVariants($props, $slot))
            ->concat($this->resolvePassthrough($props, $slot));

        return $this->merger->merge(...$classes->toArray());
    }

    /**
     * @param array<string, string|bool|array<string, string|bool>> $props
     * @return array<string, string|bool|array<string, string|bool>>
     */
    private function applyDefaultVariants(array $props): array
    {
        /** @var array<string, string|bool> $defaults */
        $defaults = $this->defaultVariants;

        foreach ($defaults as $key => $value) {
            $props[$key] ??= $value;
        }

        return $props;
    }

    private function resolveSlot(string $slot): string
    {
        if ($slot !== '') {
            return $slot;
        }

        // If base is a slot-keyed associative array, try to infer the slot.
        if (is_array($this->base) && ! array_is_list($this->base)) {
            if (count($this->base) === 1) {
                return (string) array_key_first($this->base);
            } else {
                throw new InvalidArgumentException(
                    'Multiple slots defined but no slot specified. Available slots: ' . implode(', ', array_keys($this->base)),
                );
            }
        }

        return $slot;
    }

    /**
     * Resolve the extra classes from $props['class'] or $props['className'].
     *
     * Plain strings and indexed arrays always apply to the current slot (passthrough context).
     * Associative (slot-keyed) arrays are extracted by the current slot name,
     * matching the same shape used in variant definitions.
     *
     * @param array<string, mixed> $props
     */
    private function resolvePassthrough(array $props, string $slot): ClassNames
    {
        /** @var string|array<array-key, mixed>|bool $value */
        $value = $props['class'] ?? $props['className'] ?? '';

        if (is_bool($value) || $value === '') {
            return ClassNames::empty();
        }

        // Slot-keyed array: extract only the classes for the current slot.
        if (is_array($value) && ! array_is_list($value)) {
            return ClassNames::of($value, $slot);
        }

        // Plain string or indexed list: applies to the current slot unconditionally.
        return ClassNames::of($value, '');
    }

    /**
     * @param array<string, string|bool|array<string, string|bool>> $props
     */
    private function resolveVariants(array $props, string $slot): ClassNames
    {
        $classes = ClassNames::empty();

        /** @var array<string, array<string, string|array<string, mixed>>> $variants */
        $variants = $this->variants;

        foreach ($props as $key => $value) {
            if (is_array($value)) {
                continue;
            }

            $lookupKey = match (true) {
                $value === true => 'true',
                $value === false => 'false',
                default => $value,
            };
            $entry = $variants[$key][$lookupKey] ?? null;

            if ($entry === null) {
                continue;
            }

            $classes = $classes->concat(ClassNames::of($entry, $slot));
        }

        return $classes;
    }

    /**
     * @param array<string, string|bool|array<string, string|bool>> $props
     */
    private function resolveCompoundVariants(array $props, string $slot): ClassNames
    {
        $classes = ClassNames::empty();

        /** @var array<int, array<string, mixed>> $compoundVariants */
        $compoundVariants = $this->compoundVariants;

        foreach ($compoundVariants as $compound) {
            if (! $this->compoundMatches($props, $compound)) {
                continue;
            }

            /** @var string|array<string, mixed>|bool $classValue */
            $classValue = $compound['class'] ?? $compound['className'] ?? '';
            $classes = $classes->concat(ClassNames::of($classValue, $slot));
        }

        return $classes;
    }

    /**
     * @param array<string, string|bool|array<string, string|bool>> $props
     * @param array<string, mixed> $compound
     */
    private function compoundMatches(array $props, array $compound): bool
    {
        foreach ($compound as $key => $value) {
            if ($key === 'class') {
                continue;
            }
            if ($key === 'className') {
                continue;
            }
            if (is_array($value)) {
                if (! isset($props[$key]) || ! in_array($props[$key], $value, strict: true)) {
                    return false;
                }
            } elseif (! isset($props[$key]) || $props[$key] !== $value) {
                return false;
            }
        }

        return true;
    }
}
