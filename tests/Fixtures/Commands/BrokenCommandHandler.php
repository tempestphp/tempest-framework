<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Commands;

use Tempest\CommandBus\CommandHandler;

final readonly class BrokenCommandHandler
{
    #[CommandHandler]
    public function __invoke(
        MyBrokenCommand $command, // @mago-expect best-practices/no-unused-parameter
        string $something, // @mago-expect best-practices/no-unused-parameter
    ): void {
        return;
    }

    #[CommandHandler]
    public function noObject(
        string $something,
    ): void {
        return;
    }
}
