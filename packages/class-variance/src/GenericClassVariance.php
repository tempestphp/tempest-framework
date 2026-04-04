<?php

declare(strict_types=1);

namespace Tempest\ClassVariance;

/**
 * The single concrete ClassVariance implementation used by both cv() and tv().
 * The merger injected at construction time determines the conflict-resolution strategy.
 */
final readonly class GenericClassVariance implements ClassVariance
{
    use ResolvesVariants;

    /**
     * @param array<string, string|list<string>|array<string, string|list<string>>>|list<string>|string $base
     *   Base classes. A plain string or indexed array targets the implicit 'base' slot.
     *   An associative array is a slot-keyed map: ['base' => '...', 'label' => '...'].
     * @param array<string, array<string, string|list<string>|array<string, string|list<string>>>> $variants
     *   Variant dimensions. Keys are prop names; values map prop values to classes.
     *   Per-value classes may be a plain string (implicit 'base') or a slot-keyed array.
     * @param array<int, array<string, mixed>> $compoundVariants
     *   Each entry is a compound rule. All keys except 'class'/'className' are match
     *   conditions; 'class' holds the classes to apply when all conditions match.
     * @param array<string, string|bool> $defaultVariants
     *   Default prop values applied when a prop is not explicitly provided.
     */
    public function __construct(
        public array|string $base,
        public ClassMerger $merger,
        public array $variants = [],
        public array $compoundVariants = [],
        public array $defaultVariants = [],
    ) {}
}
