<?php

namespace Tests\Tempest;

use Tempest\Container\Container;
use Tempest\Kernel;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected Container $container;

    protected Kernel $kernel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kernel = new Kernel();

        $this->container = $this->kernel->init(__DIR__ . '/../app');
    }
}