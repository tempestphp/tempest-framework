<?php

declare(strict_types=1);

namespace Tempest\ClassVariance;

use Tempest\ClassVariance\Config\TailwindClassVarianceConfig;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

/**
 * Registers the tv() ClassMerger (Tailwind-aware) as a singleton tagged 'tv'.
 *
 * TailwindClassmap::default() is built once per container lifecycle rather
 * than on every tv() call.
 *
 * Respects a TailwindClassVarianceConfig binding when one is present in the
 * container (e.g. to supply a custom prefix, separator, or extend/override map),
 * otherwise falls back to the default configuration.
 *
 * Inject via: #[Tag('tv')] ClassMerger $merger
 */
final readonly class TvMergerInitializer implements Initializer
{
    #[Singleton(tag: 'tv')]
    public function initialize(Container $container): ClassMerger
    {
        $config = $container->has(TailwindClassVarianceConfig::class)
            ? $container->get(TailwindClassVarianceConfig::class)
            : new TailwindClassVarianceConfig();

        return $config->merger;
    }
}
