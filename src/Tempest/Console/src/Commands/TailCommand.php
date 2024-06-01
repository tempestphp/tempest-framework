<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use Fiber;
use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;

final readonly class TailCommand
{
    public function __construct(
        private TailDebugLogCommand $tailDebugLogCommand,
        private TailProjectLogCommand $tailProjectLogCommand,
        private TailServerLogCommand $tailServerLogCommand,
    ) {
    }

    #[ConsoleCommand(
        name: 'tail',
        description: 'Tail multiple logs',
    )]
    public function __invoke(
        #[ConsoleArgument(description: 'Include the project log', aliases: ['-p'])]
        ?bool $project = null,
        #[ConsoleArgument(description: 'Include the server log', aliases: ['-s'])]
        ?bool $server = null,
        #[ConsoleArgument(description: 'Include the debug log', aliases: ['-d'])]
        ?bool $debug = null,
    ): void {
        $shouldFilter =
            $project !== null
            || $server !== null
            || $debug !== null;

        /** @var array<array-key, \Tempest\Console\Commands\TailDebugLogCommand|\Tempest\Console\Commands\TailProjectLogCommand> $loggers */
        $loggers = array_filter([
            ($shouldFilter === false || $project) ? $this->tailProjectLogCommand : null,
            ($shouldFilter === false || $server) ? $this->tailServerLogCommand : null,
            ($shouldFilter === false || $debug) ? $this->tailDebugLogCommand : null,
        ]);

        /** @var Fiber[] $fibers */
        $fibers = [];

        foreach ($loggers as $key => $logger) {
            $fiber = new Fiber(fn () => ($logger)());
            $fibers[$key] = $fiber;
            $fiber->start();
        }

        while ($fibers !== []) {
            foreach ($fibers as $key => $fiber) {
                if ($fiber->isSuspended()) {
                    $fiber->resume();
                }

                if ($fiber->isTerminated()) {
                    unset($fibers[$key]);
                }
            }
        }
    }
}
