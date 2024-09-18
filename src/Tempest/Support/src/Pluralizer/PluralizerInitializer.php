<?php

declare(strict_types=1);

namespace Tempest\Support\Pluralizer;

use Tempest\Container\Container;
use Tempest\Container\Initializer;

final readonly class PluralizerInitializer implements Initializer
{
    public function initialize(Container $container): Pluralizer
    {
        return new InflectorPluralizer();
    }
}
