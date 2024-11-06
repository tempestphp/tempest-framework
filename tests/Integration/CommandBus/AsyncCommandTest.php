<?php

namespace Tests\Tempest\Integration\CommandBus;

use Tempest\CommandBus\AsyncCommandRepositories\MemoryRepository;
use Tempest\CommandBus\AsyncCommandRepository;
use Tests\Tempest\Fixtures\Handlers\MyAsyncCommandHandler;
use Tests\Tempest\Integration\CommandBus\Fixtures\MyAsyncCommand;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use function Tempest\command;

final class AsyncCommandTest extends FrameworkIntegrationTestCase
{
    private MemoryRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new MemoryRepository();

        $this->container->singleton(
            AsyncCommandRepository::class,
            fn () => $this->repository
        );

        MyAsyncCommandHandler::$isHandled = false;
    }

    public function test_async_commands_are_stored_and_handled_afterwards(): void
    {
        command(new MyAsyncCommand('Brent'));

        $uuids = $this->repository->all();

        $this->assertCount(1, $uuids);
        $uuid = $uuids[0];
        $command = $this->repository->find($uuid);
        $this->assertSame('Brent', $command->name);
        $this->assertFalse(MyAsyncCommandHandler::$isHandled);

        $this->console
            ->call("command:handle {$uuid}")
            ->assertSee('Done');

        $this->assertEmpty($this->repository->all());
        $this->assertTrue(MyAsyncCommandHandler::$isHandled);
    }
}