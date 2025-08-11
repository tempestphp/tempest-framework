<?php

declare(strict_types=1);

namespace Tempest\Framework\Commands;

use Composer\Semver\Semver;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Tempest\Console\Console;
use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ExitCode;
use Tempest\Container\Singleton;
use Tempest\Core\Kernel;

#[Singleton]
final readonly class UpgradeCommand
{
    public function __construct(
        private Console $console,
    ) {}

    #[ConsoleCommand(
        name: 'upgrade:tempest',
        description: 'Upgrades the application to the latest version',
    )]
    public function __invoke(
        #[ConsoleArgument(description: 'Upgrade to a specific version', help: 'If not specified, the latest version is used')]
        string $version = Kernel::VERSION,
        #[ConsoleArgument(description: 'Dry run the upgrade')]
        bool $dryRun = false,
        #[ConsoleArgument(description: 'Verbose output')]
        bool $_verbose = false,
    ): ExitCode {
        $command = 'vendor/bin/rector --no-ansi --no-progress-bar --no-diffs';
        if ($dryRun) {
            $this->console->info('Dry run enabled');
            $command .= ' --dry-run';
        }

        $rectors = $this->getRectorsForVersion($version);
        if (! $rectors->valid()) {
            $this->console->info('No rectors found for this version');

            return ExitCode::SUCCESS;
        }

        foreach ($rectors as $rule) {
            $command .= " --only=\"{$rule}\"";
        }

        $this->console->info("Running <code>{$command}</code>");
        try {
            $processed = Process::fromShellCommandline($command)->mustRun();
        } catch (ProcessFailedException $e) {
            $this->console->error($e->getProcess()->getErrorOutput());

            return ExitCode::ERROR;
        }

        $this->console->info(trim($processed->getOutput()));

        return ExitCode::SUCCESS;
    }

    private function getRectorsForVersion(string $version): \Generator
    {
        return match (true) {
            Semver::satisfies($version, '^1.0.0') => [],
            Semver::satisfies($version, '^2.0.0') => [
                yield 'Rector\Renaming\Rector\Name\RenameClassRector',
            ],
            default => [],
        };
    }
}
