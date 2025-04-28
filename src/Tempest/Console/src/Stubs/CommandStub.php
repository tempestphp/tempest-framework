<?php

declare(strict_types=1);

namespace Tempest\Console\Stubs;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Discovery\SkipDiscovery;

#[SkipDiscovery]
final class CommandStub
{
    public function __construct(
        private Console $console,
    ) {}

    #[ConsoleCommand(name: 'dummy-command-slug')]
    public function __invoke(): void
    {
        $this->console->success('Done!');
    }
}
