<?php

declare(strict_types=1);

namespace App\Handlers;

use App\Commands\MyCommand;
use Tempest\Bus\CommandHandler;

class MyCommandHandler
{
    #[CommandHandler]
    public function __invoke(MyCommand $command): void
    {
    }
}
