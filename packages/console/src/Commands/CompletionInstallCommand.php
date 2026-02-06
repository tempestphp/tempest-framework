<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use Symfony\Component\Filesystem\Path;
use Tempest\Console\Actions\ResolveShell;
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
        private ResolveShell $resolveShell,
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
        ?Shell $shell = null,
        #[ConsoleArgument(
            description: 'Skip confirmation prompts',
            aliases: ['-f'],
        )]
        bool $force = false,
    ): ExitCode {
        $shell ??= ($this->resolveShell)('Which shell do you want to install completions for?');

        if ($shell === null) {
            $this->console->error('Could not detect shell. Please specify one using the --shell option. Possible values are: zsh, bash.');

            return ExitCode::ERROR;
        }

        $sourcePath = $this->getSourcePath($shell);
        $targetDir = $shell->getCompletionsDirectory();
        $targetPath = $shell->getInstalledCompletionPath();

        if (! Filesystem\is_file($sourcePath)) {
            $this->console->error("Completion script not found: {$sourcePath}");

            return ExitCode::ERROR;
        }

        if (! $force) {
            $this->console->info("Installing {$shell->value} completions");
            $this->console->keyValue('Source', $sourcePath);
            $this->console->keyValue('Target', $targetPath);
            $this->console->writeln();

            if (! $this->console->confirm('Proceed with installation?', default: true)) {
                $this->console->warning('Installation cancelled.');

                return ExitCode::CANCELLED;
            }
        }

        Filesystem\ensure_directory_exists($targetDir);

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
        $this->console->instructions($shell->getPostInstallInstructions());

        return ExitCode::SUCCESS;
    }

    private function getSourcePath(Shell $shell): string
    {
        return Path::canonicalize(
            path(__DIR__, '..', $shell->getSourceFilename())->toString(),
        );
    }
}
