<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use Symfony\Component\Filesystem\Path;
use Tempest\Console\Console;
use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\Enums\Shell;
use Tempest\Console\ExitCode;
use Tempest\Support\Filesystem;

use function Tempest\Support\path;

final readonly class CompletionInstallCommand
{
    public function __construct(
        private Console $console,
    ) {}

    #[ConsoleCommand(
        name: 'completion:install',
        description: 'Install shell completion for Tempest',
    )]
    public function __invoke(
        #[ConsoleArgument(
            description: 'The shell to install completions for (zsh, bash)',
            aliases: ['-s'],
        )]
        ?string $shell = null,
        #[ConsoleArgument(
            description: 'Skip confirmation prompts',
            aliases: ['-f'],
        )]
        bool $force = false,
    ): ExitCode {
        $shellEnum = $this->resolveShell($shell);

        if ($shellEnum === null) {
            $this->console->error('Could not determine shell. Please specify with --shell=zsh or --shell=bash');

            return ExitCode::ERROR;
        }

        $sourcePath = $this->getSourcePath($shellEnum);
        $targetDir = $shellEnum->getCompletionsDirectory();
        $targetPath = $shellEnum->getInstalledCompletionPath();

        if (! Filesystem\is_file($sourcePath)) {
            $this->console->error("Completion script not found: {$sourcePath}");

            return ExitCode::ERROR;
        }

        if (! $force) {
            $this->console->info("Installing {$shellEnum->value} completions");
            $this->console->keyValue('Source', $sourcePath);
            $this->console->keyValue('Target', $targetPath);
            $this->console->writeln();

            if (! $this->console->confirm('Proceed with installation?', default: true)) {
                $this->console->warning('Installation cancelled.');

                return ExitCode::CANCELLED;
            }
        }

        if (! Filesystem\is_directory($targetDir)) {
            Filesystem\create_directory($targetDir);
            $this->console->success("Created directory: {$targetDir}");
        }

        if (Filesystem\is_file($targetPath)) {
            if (! $force && ! $this->console->confirm('Completion file already exists. Overwrite?', default: false)) {
                $this->console->warning('Installation cancelled.');

                return ExitCode::CANCELLED;
            }
        }

        Filesystem\copy_file($sourcePath, $targetPath, overwrite: true);
        $this->console->success("Installed completion script to: {$targetPath}");

        $this->console->writeln();
        $this->console->info('Next steps:');
        $this->console->instructions($shellEnum->getPostInstallInstructions());

        return ExitCode::SUCCESS;
    }

    private function resolveShell(?string $shell): ?Shell
    {
        if ($shell !== null) {
            return Shell::tryFrom(strtolower($shell));
        }

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
                question: 'Which shell do you want to install completions for?',
                options: $options,
                default: $detected?->value,
            );

            return Shell::tryFrom($choice);
        }

        return $detected;
    }

    private function getSourcePath(Shell $shell): string
    {
        return Path::canonicalize(
            path(__DIR__, '..', $shell->getSourceFilename())->toString(),
        );
    }
}
