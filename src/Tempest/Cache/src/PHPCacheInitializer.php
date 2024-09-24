<?php

namespace Tempest\Cache;

use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

final readonly class PHPCacheInitializer implements Initializer
{
    #[Singleton(tag: 'php')]
    public function initialize(Container $container): Cache
    {
        return new Cache(new PhpFilesAdapter(
            namespace: 'php',
            defaultLifetime: 0,
            directory: __DIR__ . '/../../../../.cache'
        ));
    }
}