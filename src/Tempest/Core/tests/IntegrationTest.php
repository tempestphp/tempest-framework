<?php

declare(strict_types=1);

namespace Tests\Tempest;

use PHPUnit\Framework\TestCase;
use Tempest\AppConfig;
use Tempest\Container\Container;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Kernel;

abstract class IntegrationTest extends TestCase
{
    protected AppConfig $appConfig;
    protected Kernel $kernel;
    protected Container $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->appConfig = new AppConfig(
            root: __DIR__ . '/..',
            discoveryCache: true,
            discoveryLocations: [
                new DiscoveryLocation(
                    'App\\',
                    __DIR__ . '/../app/',
                ),
            ],
        );

        $this->kernel = new Kernel($this->appConfig);
        $this->container = $this->kernel->init();
    }
}
