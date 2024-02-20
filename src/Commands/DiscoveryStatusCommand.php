<?php

namespace Tempest\Commands;

use Tempest\AppConfig;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleStyle;
use Tempest\Interface\ConsoleOutput;

final readonly class DiscoveryStatusCommand
{
    public function __construct(
        private ConsoleOutput $output,
        private AppConfig $appConfig,
    ) {}

    #[ConsoleCommand(name: 'discovery:status')]
    public function __invoke()
    {
        foreach ($this->appConfig->discoveryClasses as $discoveryClass) {
            $this->output->success(ConsoleStyle::FG_BLUE($discoveryClass));
        }

        $this->output->writeln('');

        foreach ($this->appConfig->discoveryLocations as $discoveryLocation) {
            $this->output->success(ConsoleStyle::FG_BLUE($discoveryLocation->path));
        }
    }
}