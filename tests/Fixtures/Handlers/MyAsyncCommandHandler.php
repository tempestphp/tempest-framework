<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Handlers;

use Tempest\CommandBus\CommandHandler;
use Tests\Tempest\Integration\CommandBus\Fixtures\MyAsyncCommand;

final class MyAsyncCommandHandler
{
    public static bool $isHandled = false;

    #[CommandHandler]
    public function __invoke(MyAsyncCommand $command): void
    {
        self::$isHandled = true;
    }
}
