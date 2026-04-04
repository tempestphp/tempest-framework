<?php

declare(strict_types=1);

namespace Tempest\ClassVariance;

use Tempest\ClassVariance\Config\ClassVarianceConfig;
use Tempest\ClassVariance\Config\GenericClassVarianceConfig;
use Tempest\ClassVariance\Config\TailwindClassVarianceConfig;
use Tempest\Container\GenericContainer;

/**
 * Create a class variance authority with generic (non-Tailwind) merging.
 *
 * The default merger splits on '-' and keeps the last class per prefix group.
 * Pass a ClassGroupMap via GenericClassVarianceConfig to declare explicit
 * conflict groups for classes that share no common prefix.
 *
 * @param array<string, string|list<string>|array<string, string|list<string>>>|list<string>|string $base
 *   Base classes. A plain string or indexed array implicitly targets the 'base'
 *   slot. An associative array is a slot-keyed map.
 * @param array<string, array<string, string|list<string>|array<string, string|list<string>>>> $variants
 * @param array<int, array<string, mixed>> $compoundVariants
 * @param array<string, string|bool> $defaultVariants
 */
function cv(
    array|string $base,
    array $variants = [],
    array $compoundVariants = [],
    array $defaultVariants = [],
    ?ClassVarianceConfig $config = null,
): ClassVariance {
    $container = GenericContainer::instance();
    $config ??= $container?->has(GenericClassVarianceConfig::class)
        ? $container->get(GenericClassVarianceConfig::class)
        : new GenericClassVarianceConfig();

    return new GenericClassVariance($base, $config->merger, $variants, $compoundVariants, $defaultVariants);
}

/**
 * Create a class variance authority with Tailwind-aware merging.
 *
 * Ships with the full Tailwind CSS class group definitions and conflict rules.
 * Pass a TailwindClassVarianceConfig to extend/override groups for custom
 * plugins or a non-standard Tailwind prefix / separator.
 *
 * @param array<string, string|list<string>|array<string, string|list<string>>>|list<string>|string $base
 *   Base classes. A plain string or indexed array implicitly targets the 'base'
 *   slot. An associative array is a slot-keyed map.
 * @param array<string, array<string, string|list<string>|array<string, string|list<string>>>> $variants
 * @param array<int, array<string, mixed>> $compoundVariants
 * @param array<string, string|bool> $defaultVariants
 */
function tv(
    array|string $base,
    array $variants = [],
    array $compoundVariants = [],
    array $defaultVariants = [],
    ?ClassVarianceConfig $config = null,
): ClassVariance {
    $container = GenericContainer::instance();
    $config ??= $container?->has(TailwindClassVarianceConfig::class)
        ? $container->get(TailwindClassVarianceConfig::class)
        : new TailwindClassVarianceConfig();

    return new GenericClassVariance($base, $config->merger, $variants, $compoundVariants, $defaultVariants);
}
