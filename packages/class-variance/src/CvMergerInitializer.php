<?php

declare(strict_types=1);

namespace Tempest\ClassVariance;

use Tempest\ClassVariance\Config\GenericClassVarianceConfig;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

/**
 * Registers the cv() ClassMerger (separator-based) as a singleton tagged 'cv'.
 *
 * Respects a GenericClassVarianceConfig binding when one is present in the
 * container, otherwise falls back to the default configuration.
 *
 * Inject via: #[Tag('cv')] ClassMerger $merger
 */
final readonly class CvMergerInitializer implements Initializer
{
    #[Singleton(tag: 'cv')]
    public function initialize(Container $container): ClassMerger
    {
        $config = $container->has(GenericClassVarianceConfig::class)
            ? $container->get(GenericClassVarianceConfig::class)
            : new GenericClassVarianceConfig();

        return $config->merger;
    }
}
