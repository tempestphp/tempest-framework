<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use Tempest\Console\Actions\ResolveShell;
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
        private ResolveShell $resolveShell,
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
        $shell ??= ($this->resolveShell)('Which shell completions do you want to uninstall?');

        if ($shell === null) {
            $this->console->error('Could not detect shell. Please specify one using the --shell option. Possible values are: zsh, bash.');

            return ExitCode::ERROR;
        }

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

        if ($shell === Shell::ZSH) {
            $this->cleanupZshCache();
        }

        $this->console->writeln();
        $this->console->info('Remember to remove any related lines from your shell configuration:');
        $this->console->keyValue('Config file', $shell->getRcFile());

        return ExitCode::SUCCESS;
    }

    private function cleanupZshCache(): void
    {
        $home = $_SERVER['HOME'] ?? $_ENV['HOME'] ?? null;

        if ($home === null) {
            return;
        }

        $cacheFiles = glob("{$home}/.zcompdump*") ?: [];

        foreach ($cacheFiles as $file) {
            Filesystem\delete_file($file);
        }

        if ($cacheFiles !== []) {
            $this->console->info('Cleared zsh completion cache (~/.zcompdump*)');
        }

        $this->console->writeln();
        $this->console->info('Run this to clear completions in your current shell:');
        $this->console->writeln();
        $this->console->writeln("  unset '_patcomps[php]' '_patcomps[tempest]' '_patcomps[*/tempest]' 2>/dev/null");
        $this->console->writeln();
        $this->console->info('Or restart your shell: exec zsh');
    }
}
