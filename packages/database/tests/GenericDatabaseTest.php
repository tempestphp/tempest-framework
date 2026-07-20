<?php

declare(strict_types=1);

namespace Tempest\Database\Tests;

use Exception;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Container\GenericContainer;
use Tempest\Database\Connection\Connection;
use Tempest\Database\GenericDatabase;
use Tempest\Database\Transactions\GenericTransactionManager;
use Tempest\EventBus\EventBusConfig;
use Tempest\EventBus\GenericEventBus;
use Tempest\EventBus\Testing\FakeEventBus;
use Tempest\Mapper\SerializerFactory;

/**
 * @internal
 */
final class GenericDatabaseTest extends TestCase
{
    #[Test]
    public function it_executes_transactions(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection
            ->expects($this->once())
            ->method('beginTransaction')
            ->withAnyParameters()
            ->willReturn(true);
        $connection
            ->expects($this->once())
            ->method('commit')
            ->withAnyParameters()
            ->willReturn(true);

        $container = new GenericContainer();
        $eventBusConfig = new EventBusConfig();
        $eventBus = new FakeEventBus(new GenericEventBus($container, $eventBusConfig), $eventBusConfig);

        $database = new GenericDatabase(
            $connection,
            new GenericTransactionManager($connection),
            new SerializerFactory(new GenericContainer()),
            $eventBus,
        );

        $result = $database->withinTransaction(fn () => true);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_rolls_back_transactions_on_failure(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection
            ->expects($this->once())
            ->method('beginTransaction')
            ->withAnyParameters()
            ->willReturn(true);
        $connection
            ->expects($this->once())
            ->method('rollback')
            ->withAnyParameters()
            ->willReturn(true);

        $container = new GenericContainer();
        $eventBusConfig = new EventBusConfig();
        $eventBus = new FakeEventBus(new GenericEventBus($container, $eventBusConfig), $eventBusConfig);

        $database = new GenericDatabase(
            $connection,
            new GenericTransactionManager($connection),
            new SerializerFactory(new GenericContainer()),
            $eventBus,
        );

        $result = $database->withinTransaction(function (): never {
            throw new Exception();
        });

        $this->assertFalse($result);
    }
}
