<?php

namespace Tempest\Console\Commands;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Core\ExperimentalConfig;

final class AboutExperimentalCommand
{
    use HasConsole;

    public function __construct(
        private readonly ExperimentalConfig $experimentalConfig,
    ) {}

    #[ConsoleCommand]
    public function __invoke(): void
    {
        $this->console->info('Experimental features are not stable and may change at any time. Read more about them here: https://tempestphp.com/1.x/extra-topics/roadmap');

        foreach ($this->experimentalConfig->experimentalFeatures as $experimental) {
            $this->console->header($experimental->name);
            $this->console->writeln($experimental->description);
        }
    }
}
