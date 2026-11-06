<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

use Tempest\Container\Container;
use Tempest\Container\Initializer;

final readonly class ResolvedTwiceParentInitializer implements Initializer
{
    public function initialize(Container $container): ResolvedTwiceParent
    {
        // One shared chain - constructor parameters each resolve against their own clone of it.
        return new ResolvedTwiceParent(
            $container->get(ResolvedTwiceLeaf::class),
            $container->get(ResolvedTwiceLeaf::class),
        );
    }
}
