<?php

declare(strict_types=1);

namespace Tempest\Testing;

use PHPUnit\Framework\TestCase;
use Tempest\AppConfig;
use Tempest\Application\Kernel;
use Tempest\Container\Container;
use Tempest\Testing\Console\ConsoleCommandTester;
use Tempest\Testing\Http\HttpRouterTester;

abstract class IntegrationTest extends TestCase
{
    private Kernel $kernel;

    private Container $container;

    protected ConsoleCommandTester $console;

    protected HttpRouterTester $http;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kernel = new Kernel(__DIR__ . '/../../', new AppConfig(
            discoveryCache: true,
        ));

        $this->container = $this->kernel->init();

        $this->console = $this->container->get(ConsoleCommandTester::class);
        $this->http = $this->container->get(HttpRouterTester::class);
    }
}
