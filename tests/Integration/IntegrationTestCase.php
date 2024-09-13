<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration;

use PHPUnit\Framework\TestCase;
use Tempest\Container\Container;
use Tempest\Core\AppConfig;
use Tempest\Core\DiscoveryLocation;
use Tempest\Core\Kernel;

abstract class IntegrationTestCase extends TestCase
{
    protected AppConfig $appConfig;

    protected Kernel $kernel;

    protected Container $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kernel = new Kernel(
            root: getcwd(),
            discoveryLocations: [
                new DiscoveryLocation(
                    'Tests\\Tempest\\Fixtures\\',
                    __DIR__ . '/../Fixtures/',
                ),
            ],
            discoveryCache: true,
        );

        $this->container = ($this->kernel)->container;
    }
}
