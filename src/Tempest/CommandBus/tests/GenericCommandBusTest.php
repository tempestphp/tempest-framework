<?php

declare(strict_types=1);

namespace Tempest\CommandBus\Tests;

use PHPUnit\Framework\TestCase;
use Tempest\CommandBus\CommandBus;
use Tempest\CommandBus\CommandBusConfig;
use Tempest\CommandBus\CommandHandler;
use Tempest\CommandBus\CommandHandlerAlreadyExists;
use Tempest\CommandBus\CommandHandlerNotFound;
use Tempest\CommandBus\GenericCommandBus;
use Tempest\CommandBus\Tests\Fixtures\CreateUserCommand;
use Tempest\CommandBus\Tests\Fixtures\CreateUserCommandHandler;
use Tempest\CommandBus\Tests\Fixtures\DeleteUserCommand;
use Tempest\Container\GenericContainer;
use Tempest\Reflection\ClassReflector;

/**
 * @internal
 */
final class GenericCommandBusTest extends TestCase
{
    private CommandBusConfig $config;

    private CommandBus $commandBus;

    public function test_getting_command_handler_that_exists(): void
    {
        $command = new CreateUserCommand('Jim', 'Halpert');

        $this->commandBus->dispatch($command);

        $this->assertCount(1, $this->commandBus->getHistory());
        $this->assertSame($command, $this->commandBus->getHistory()[0]);
    }

    public function test_exception_is_thrown_when_command_handler_doesnt_exist(): void
    {
        $command = new DeleteUserCommand(12);

        $this->expectExceptionObject(
            new CommandHandlerNotFound($command)
        );

        $this->commandBus->dispatch($command);
    }

    public function test_exception_is_thrown_when_command_handler_already_exist(): void
    {
        $createUserCommandHandlerClass = new ClassReflector(CreateUserCommandHandler::class);
        $createUserCommandHandlerMethod = $createUserCommandHandlerClass->getMethod('__invoke');
        $createUserCommandHandler = $createUserCommandHandlerMethod->getAttribute(CommandHandler::class);

        $this->expectException(CommandHandlerAlreadyExists::class);

        $this->config->addHandler(
            commandHandler: $createUserCommandHandler,
            commandName: CreateUserCommand::class,
            handler: $createUserCommandHandlerClass->getMethod('double'),
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        // TODO: I'd like to make this easier to setup.
        $this->config = new CommandBusConfig();

        $createUserCommandHandlerClass = new ClassReflector(CreateUserCommandHandler::class);
        $createUserCommandHandlerMethod = $createUserCommandHandlerClass->getMethod('__invoke');
        $createUserCommandHandler = $createUserCommandHandlerMethod->getAttribute(CommandHandler::class);

        $this->config->addHandler(
            commandHandler: $createUserCommandHandler,
            commandName: CreateUserCommand::class,
            handler: $createUserCommandHandlerMethod,
        );

        $this->commandBus = new GenericCommandBus(
            container: new GenericContainer(),
            commandBusConfig: $this->config,
        );
    }
}
