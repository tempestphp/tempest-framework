<?php

declare(strict_types=1);

namespace Tempest\Intl\Pluralizer;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

final readonly class PluralizerInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): Pluralizer
    {
        return new InflectorPluralizer();
    }
}
