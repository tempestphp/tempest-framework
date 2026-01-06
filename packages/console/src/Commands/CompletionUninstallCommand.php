<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use Tempest\Console\Console;
use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\Enums\Shell;
use Tempest\Console\ExitCode;
use Tempest\Support\Filesystem;

final readonly class CompletionUninstallCommand
{
    public function __construct(
        private Console $console,
    ) {}

    #[ConsoleCommand(
        name: 'completion:uninstall',
        description: 'Uninstall shell completion for Tempest',
    )]
    public function __invoke(
        #[ConsoleArgument(
            description: 'The shell to uninstall completions for (zsh, bash)',
            aliases: ['-s'],
        )]
        ?Shell $shell = null,
        #[ConsoleArgument(
            description: 'Skip confirmation prompts',
            aliases: ['-f'],
        )]
        bool $force = false,
    ): ExitCode {
        $shell ??= $this->resolveShell();

        $targetPath = $shell->getInstalledCompletionPath();

        if (! Filesystem\is_file($targetPath)) {
            $this->console->warning("Completion file not found: {$targetPath}");
            $this->console->info('Nothing to uninstall.');

            return ExitCode::SUCCESS;
        }

        if (! $force) {
            $this->console->info("Uninstalling {$shell->value} completions");
            $this->console->keyValue('File', $targetPath);
            $this->console->writeln();

            if (! $this->console->confirm('Proceed with uninstallation?', default: true)) {
                $this->console->warning('Uninstallation cancelled.');

                return ExitCode::CANCELLED;
            }
        }

        Filesystem\delete_file($targetPath);
        $this->console->success("Removed completion script: {$targetPath}");

        $this->console->writeln();
        $this->console->info('Remember to remove any related lines from your shell configuration:');
        $this->console->keyValue('Config file', $shell->getRcFile());

        return ExitCode::SUCCESS;
    }

    private function resolveShell(): Shell
    {
        $detected = Shell::detect();

        if ($this->console->supportsPrompting()) {
            $options = [];

            foreach (Shell::cases() as $shellCase) {
                $label = $shellCase->value;

                if ($shellCase === $detected) {
                    $label .= ' (current)';
                }

                $options[$shellCase->value] = $label;
            }

            $choice = $this->console->ask(
                question: 'Which shell completions do you want to uninstall?',
                options: $options,
                default: $detected?->value,
            );

            return Shell::from($choice);
        }

        return $detected;
    }
}
