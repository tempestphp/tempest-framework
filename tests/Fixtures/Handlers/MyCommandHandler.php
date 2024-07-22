<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Handlers;

use Tempest\CommandBus\CommandHandler;
use Tests\Tempest\Fixtures\Commands\MyCommand;

class MyCommandHandler
{
    #[CommandHandler]
    public function __invoke(MyCommand $command): void
    {
    }
}
