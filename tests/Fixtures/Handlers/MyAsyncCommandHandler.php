<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Handlers;

use Exception;
use Tempest\CommandBus\CommandHandler;
use Tempest\CommandBus\Tests\Integration\Fixtures\MyAsyncCommand;
use Tempest\CommandBus\Tests\Integration\Fixtures\MyFailingAsyncCommand;

final class MyAsyncCommandHandler
{
    public static bool $isHandled = false;

    #[CommandHandler]
    public function onMyAsyncCommand(MyAsyncCommand $command): void
    {
        self::$isHandled = true;
    }

    #[CommandHandler]
    public function onMyFailingAsyncCommand(MyFailingAsyncCommand $command): void
    {
        throw new Exception('Failed command');
    }
}
