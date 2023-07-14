<?php

namespace Tests\Tempest;

use Tempest\Application\Kernel;
use Tempest\Http\Method;
use Tempest\Interfaces\Container;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected Container $container;

    protected Kernel $kernel;

    protected function setUp(): void
    {
        parent::setUp();

        $databasePath = __DIR__ . '/../app/database.sqlite';
        $cleanDatabasePath = __DIR__ . '/../app/database-clean.sqlite';

        @unlink($databasePath);
        copy($cleanDatabasePath, $databasePath);

        $this->kernel = new Kernel();

        $this->container = $this->kernel->init(
            __DIR__ . '/../app',
            'App\\',
        );

        $this->container->addInitializer(new TestServerInitializer());
    }

    protected function server(
        Method $method = Method::GET,
        string $uri = '/',
        array $body = [],
    ): void {
        $this->container->addInitializer(new TestServerInitializer(
            method: $method,
            uri: $uri,
            body: $body,
        ));
    }
}
