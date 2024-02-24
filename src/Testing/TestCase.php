<?php

declare(strict_types=1);

namespace Tempest\Testing;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Tempest\AppConfig;
use Tempest\Application\Kernel;
use Tempest\Container\Container;
use Tempest\Testing\Http\TestsHttpRequests;

abstract class TestCase extends BaseTestCase
{
    use TestsHttpRequests;

    private Kernel $kernel;

    private Container $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kernel = new Kernel(__DIR__ . '/../../', new AppConfig(
            discoveryCache: true,
        ));

        $this->container = $this->kernel->init();
    }
}
