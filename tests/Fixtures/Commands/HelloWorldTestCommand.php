<?php

namespace Tests\Tempest\Fixtures\Commands;

use Tempest\Console\ConsoleCommand;

final readonly class HelloWorldTestCommand
{
    #[ConsoleCommand]
    public function __invoke(): void
    {

    }
}