<?php

declare(strict_types=1);

namespace Tests\Tempest\Console\Fixtures;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\Middleware\CautionMiddleware;

final readonly class CautionCommand
{
    public function __construct(private Console $console)
    {
    }

    #[ConsoleCommand(
        middleware: [CautionMiddleware::class]
    )]
    public function __invoke(): void
    {
        $this->console->error("CAUTION confirmed");
    }
}
