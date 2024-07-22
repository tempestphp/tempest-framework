<?php

declare(strict_types=1);

namespace Tempest\Console\Middleware;

use Tempest\Console\Actions\RenderConsoleCommand;
use Tempest\Console\Console;
use Tempest\Console\ConsoleConfig;
use Tempest\Console\ConsoleMiddleware;
use Tempest\Console\ExitCode;
use Tempest\Console\Initializers\Invocation;
use Tempest\Core\Application\AppConfig;

final readonly class OverviewMiddleware implements ConsoleMiddleware
{
    public function __construct(
        private Console $console,
        private AppConfig $appConfig,
        private ConsoleConfig $consoleConfig,
    ) {
    }

    public function __invoke(Invocation $invocation, callable $next): ExitCode
    {
        if (! $invocation->argumentBag->getCommandName()) {
            $this->renderOverview(showHidden: $invocation->argumentBag->has('--all', '-a'));

            return ExitCode::SUCCESS;
        }

        return $next($invocation);
    }

    private function renderOverview(bool $showHidden = false): void
    {
        $this->console
            ->writeln("<h1>{$this->consoleConfig->name}</h1>")
            ->when(
                expression: $this->appConfig->discoveryCache,
                callback: fn (Console $console) => $console->error('Discovery cache is enabled!')
            );

        /** @var \Tempest\Console\ConsoleCommand[][] $commands */
        $commands = [];

        foreach ($this->consoleConfig->commands as $consoleCommand) {
            if ($showHidden === false && $consoleCommand->hidden) {
                continue;
            }

            $parts = explode(':', $consoleCommand->getName());

            $group = count($parts) > 1 ? $parts[0] : 'General';

            $commands[$group][$consoleCommand->getName()] = $consoleCommand;
        }

        ksort($commands);

        foreach ($commands as $group => $commandsForGroup) {
            $this->console
                ->writeln()
                ->writeln('<h2>' . ucfirst($group) . '</h2>');

            foreach ($commandsForGroup as $consoleCommand) {
                (new RenderConsoleCommand($this->console))($consoleCommand);
            }
        }
    }
}
