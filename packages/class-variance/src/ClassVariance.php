<?php

declare(strict_types=1);

namespace Tempest\ClassVariance;

/**
 * A callable that resolves CSS class strings from a variant definition.
 * Implementations hold base classes, variants, compound variants, and defaults,
 * and produce a merged class string for a given set of active props and slot.
 */
interface ClassVariance
{
    /**
     * Resolve the class string for the given props and slot.
     *
     * @param array<string, string|bool> $props Active variant prop values.
     * @param string $slot Named slot to resolve (e.g. 'base', 'label'). When omitted
     *   and the base defines a single slot, that slot is inferred automatically.
     */
    public function __invoke(array $props = [], string $slot = ''): string;
}
