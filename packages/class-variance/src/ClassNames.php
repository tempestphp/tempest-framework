<?php

declare(strict_types=1);

namespace Tempest\ClassVariance;

/**
 * Immutable value object representing a parsed, flat list of CSS class tokens.
 *
 * Handles all input shapes that appear in variant definitions:
 *   - Plain string                → implicitly targets the 'base' slot only
 *   - Indexed array of strings    → flat class list, implicitly 'base'
 *   - Associative array           → slot-keyed map; extract by requested slot
 *   - Bool                        → always empty (booleans are not class names)
 *
 * @internal
 */
final readonly class ClassNames
{
    /** @param list<string> $items */
    private function __construct(
        private array $items = [],
    ) {}

    /**
     * Parse any variant value into a ClassNames instance for the given slot.
     *
     * A plain string or indexed array implicitly targets the 'base' slot.
     * Passing $slot = '' is the passthrough context (e.g. extra $props['class'])
     * and always emits the classes regardless of slot.
     *
     * @param string|array<array-key, mixed>|bool $input
     */
    public static function of(string|array|bool $input, string $slot = ''): self
    {
        if (is_bool($input)) {
            return new self();
        }

        if (is_string($input)) {
            // Plain string implicitly targets 'base'. Skip for any other explicit slot.
            if ($slot !== '' && $slot !== 'base') {
                return new self();
            }

            return new self(self::tokenise($input));
        }

        // Indexed (list) array — implicitly 'base', same rule as plain string.
        if (array_is_list($input)) {
            if ($slot !== '' && $slot !== 'base') {
                return new self();
            }

            $items = [];
            foreach ($input as $entry) {
                if (is_string($entry)) {
                    array_push($items, ...self::tokenise($entry));
                }
            }

            return new self($items);
        }

        // Associative (slot-keyed) array — extract the requested slot's value.
        $slotValue = $input[$slot] ?? null;

        if ($slotValue === null || is_bool($slotValue)) {
            return new self();
        }

        // Recurse with slot='' so the plain-string / list rules above apply
        // without the 'base'-only guard (we have already resolved the slot key).
        return self::of($slotValue, '');
    }

    public static function empty(): self
    {
        return new self();
    }

    public function concat(self $other): self
    {
        return new self([...$this->items, ...$other->items]);
    }

    /** @return list<string> */
    public function toArray(): array
    {
        return array_values(array_unique(
            array_filter($this->items, static fn (string $c) => $c !== ''),
        ));
    }

    public function toString(): string
    {
        return implode(' ', $this->toArray());
    }

    /** @return list<string> */
    private static function tokenise(string $value): array
    {
        return array_values(array_filter(
            explode(' ', $value),
            static fn (string $t) => $t !== '',
        ));
    }
}
