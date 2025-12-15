<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\Enums\Shell;
use Tempest\Console\HasConsole;
use Tempest\Console\ShellCompletionSupport;

use function Tempest\Support\Filesystem\create_directory;
use function Tempest\Support\Filesystem\is_directory;
use function Tempest\Support\Filesystem\is_file;
use function Tempest\Support\Filesystem\read_file;
use function Tempest\Support\Filesystem\write_file;
use function Tempest\Support\str;

final class CompletionInstallCommand
{
    use HasConsole;
    use ShellCompletionSupport;

    #[ConsoleCommand(
        name: 'completion:install',
        description: 'Install shell completion for Tempest commands',
    )]
    public function __invoke(?string $shell = null): void
    {
        $targetShell = $this->resolveShell($shell);

        if ($targetShell === null) {
            $this->error('Could not detect shell. Please specify one with --shell=bash or --shell=zsh');

            return;
        }

        if (! $this->confirm("Install completion for <em>{$targetShell->value}</em>?", default: true)) {
            $this->info('Installation cancelled.');

            return;
        }

        $method = $this->ask(
            question: 'How would you like to install completion?',
            options: [
                'source' => 'Source (add source line to shell config)',
                'copy' => 'Copy (copy script to completions directory)',
                'manual' => 'Manual (show instructions only)',
            ],
        );

        $success = match ($method) {
            'source' => $this->installWithSource($targetShell),
            'copy' => $this->installWithCopy($targetShell),
            'manual' => $this->showManualInstructions($targetShell),
            default => false,
        };

        if ($success) {
            $this->writeln();
            $this->success('Shell completion installed successfully!');
        }

        if ($success || $method === 'manual') {
            $this->showReloadInstructions($targetShell);
        }
    }

    private function installWithSource(Shell $shell): bool
    {
        $rcFile = $shell->rcFile();
        $completionScriptPath = $this->getCompletionScriptPath($shell);
        $currentContent = $this->getRcContent($rcFile, $shell);

        if ($currentContent === null) {
            return false;
        }

        $sourceLine = self::COMPLETION_MARKER . PHP_EOL;
        $sourceLine .= "source \"{$completionScriptPath}\"" . PHP_EOL;

        $newContent = rtrim($currentContent) . PHP_EOL . PHP_EOL . $sourceLine;

        write_file($rcFile, $newContent);
        $this->info("Added source line to {$rcFile}");

        return true;
    }

    private function getRcContent(string $rcFile, Shell $shell): ?string
    {
        if (! is_file($rcFile)) {
            return '';
        }

        $content = read_file($rcFile);

        if (! str($content)->contains(self::COMPLETION_MARKER)) {
            return $content;
        }

        $this->warning("Completion already installed in {$rcFile}");

        if (! $this->confirm('Do you want to reinstall?', default: false)) {
            return null;
        }

        return $content |> $this->removeCompletionLines(...);
    }

    private function installWithCopy(Shell $shell): bool
    {
        $completionsDir = $shell->completionsDirectory();
        $destinationPath = $completionsDir . '/' . $shell->completionScriptName();
        $sourcePath = $this->getCompletionScriptPath($shell);

        if (! $this->ensureDirectoryExists($completionsDir)) {
            return false;
        }

        if (! $this->canWriteToDestination($destinationPath)) {
            return false;
        }

        write_file($destinationPath, read_file($sourcePath));
        $this->info("Copied completion script to {$destinationPath}");

        if ($shell === Shell::ZSH) {
            $this->ensureZshFpath($shell, $completionsDir);
        }

        return true;
    }

    private function ensureDirectoryExists(string $dir): bool
    {
        if (is_directory($dir)) {
            return true;
        }

        if (! $this->confirm("Create directory {$dir}?", default: true)) {
            return false;
        }

        create_directory($dir);

        return true;
    }

    private function canWriteToDestination(string $path): bool
    {
        if (! is_file($path)) {
            return true;
        }

        $this->warning("File already exists: {$path}");

        return $this->confirm('Do you want to overwrite it?', default: false);
    }

    private function showManualInstructions(Shell $shell): bool
    {
        $completionScriptPath = $this->getCompletionScriptPath($shell);
        $rcFile = $shell->rcFile();
        $completionsDir = $shell->completionsDirectory();

        $this->writeln();
        $this->header('Manual Installation Instructions');

        $this->writeln();
        $this->writeln('<strong>Option 1: Source the completion script</strong>');
        $this->writeln("Add this line to your {$rcFile}:");
        $this->writeln();
        $this->writeln("  <em>source \"{$completionScriptPath}\"</em>");

        $this->writeln();
        $this->writeln('<strong>Option 2: Copy to completions directory</strong>');
        $this->writeln('1. Create the completions directory (if needed):');
        $this->writeln("   <em>mkdir -p {$completionsDir}</em>");
        $this->writeln();
        $this->writeln('2. Copy the completion script:');
        $this->writeln("   <em>cp \"{$completionScriptPath}\" \"{$completionsDir}/{$shell->completionScriptName()}\"</em>");
        $this->writeln();

        if ($shell === Shell::ZSH) {
            $this->writeln("3. Add to fpath in {$rcFile} (before compinit):");
            $this->writeln("   <em>fpath=({$completionsDir} \$fpath)</em>");
        }

        return false;
    }

    private function ensureZshFpath(Shell $shell, string $completionsDir): void
    {
        $rcFile = $shell->rcFile();
        $content = is_file($rcFile) ? read_file($rcFile) : '';
        $stringable = str($content);

        if ($stringable->contains($completionsDir)) {
            $this->info("fpath already configured in {$rcFile}");

            return;
        }

        if ($stringable->contains(self::COMPLETION_MARKER)) {
            $this->warning('Completion marker found but fpath not configured. Updating...');
            $content = $content |> $this->removeCompletionLines(...);
        }

        if (! $this->confirm("Add fpath configuration to {$rcFile}?", default: true)) {
            $this->writeln();
            $this->warning('You need to manually add this to your shell config:');
            $this->writeln("  <em>fpath=({$completionsDir} \$fpath)</em>");
            $this->writeln('  <em>autoload -Uz compinit && compinit</em>');

            return;
        }

        $fpathLine = self::COMPLETION_MARKER . PHP_EOL;
        $fpathLine .= "fpath=({$completionsDir} \$fpath)" . PHP_EOL;
        $fpathLine .= 'autoload -Uz compinit && compinit' . PHP_EOL;

        write_file($rcFile, rtrim($content) . PHP_EOL . PHP_EOL . $fpathLine);
        $this->info("Added fpath configuration to {$rcFile}");
    }

    private function showReloadInstructions(Shell $shell): void
    {
        $this->writeln();
        $this->info('To activate completion, either:');
        $this->writeln('  1. Open a new terminal window, or');
        $this->writeln("  2. Run: <em>source {$shell->rcFile()}</em>");
    }
}
