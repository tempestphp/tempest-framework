<?php

declare(strict_types=1);

namespace Tempest\ClassVariance\Config;

use Tempest\ClassVariance\Classmaps\Classmap;
use Tempest\ClassVariance\Classmaps\TailwindClassmap;
use Tempest\ClassVariance\ClassMerger;
use Tempest\ClassVariance\GroupClassMerger;

/**
 * Configuration for tv() — Tailwind-aware class merging.
 *
 * Ships with the full Tailwind CSS class group definitions and conflict rules
 * ported from tailwind-merge. Custom plugin utilities or non-standard theme
 * values can be added via $extend (additive) or $override (replaces a group).
 *
 * Use $prefix when your Tailwind config sets a custom class prefix (e.g. 'tw-').
 * Use $separator when you have changed the variant separator from the default ':'.
 */
final class TailwindClassVarianceConfig implements ClassVarianceConfig
{
    public function __construct(
        public readonly string $prefix = '',
        public readonly string $separator = ':',
        public readonly ?Classmap $extend = null,
        public readonly ?Classmap $override = null,
    ) {}

    public ClassMerger $merger {
        get {
            $groups = TailwindClassmap::default();

            $extend = $this->extend;
            if ($extend instanceof Classmap) {
                $groups = $groups->extend($extend);
            }

            $override = $this->override;
            if ($override instanceof Classmap) {
                $groups = $groups->override($override);
            }

            return new GroupClassMerger($groups, $this->prefix, $this->separator);
        }
    }
}
