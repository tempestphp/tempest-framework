<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\Enums\Shell;
use Tempest\Console\HasConsole;
use Tempest\Console\ShellCompletionSupport;

use function Tempest\Support\Filesystem\delete_file;
use function Tempest\Support\Filesystem\is_file;
use function Tempest\Support\Filesystem\read_file;
use function Tempest\Support\Filesystem\write_file;
use function Tempest\Support\str;

final class CompletionUninstallCommand
{
    use HasConsole;
    use ShellCompletionSupport;

    #[ConsoleCommand(
        name: 'completion:uninstall',
        description: 'Remove shell completion for Tempest commands',
    )]
    public function __invoke(?string $shell = null): void
    {
        $targetShell = $this->resolveShell($shell);

        if ($targetShell === null) {
            $this->error('Could not detect shell. Please specify one with --shell=bash or --shell=zsh');

            return;
        }

        $installations = $this->detectInstallations($targetShell);

        if ($installations === []) {
            $this->info("No completion installation found for {$targetShell->value}");

            return;
        }

        $this->writeln('Found completion installations:');

        foreach ($installations as $type => $path) {
            $this->keyValue($type, $path);
        }

        $this->writeln();

        if (! $this->confirm('Remove all found installations?', default: true)) {
            $this->info('Uninstallation cancelled.');

            return;
        }

        $rcRemoved = ! isset($installations['rc']) || $this->removeRcInstallation($targetShell);
        $copyRemoved = ! isset($installations['copy']) || $this->removeCopiedFile($installations['copy']);

        if ($rcRemoved && $copyRemoved) {
            $this->writeln();
            $this->success('Shell completion removed successfully!');
            $this->writeln('Reload your shell to apply changes.');
        }
    }

    /**
     * @return array<string, string>
     */
    private function detectInstallations(Shell $shell): array
    {
        $installations = [];

        $rcFile = $shell->rcFile();

        if (is_file($rcFile) && str(read_file($rcFile))->contains(self::COMPLETION_MARKER)) {
            $installations['rc'] = $rcFile;
        }

        $copiedPath = $shell->completionsDirectory() . '/' . $shell->completionScriptName();

        if (is_file($copiedPath) && $this->isTempestCompletionScript($copiedPath)) {
            $installations['copy'] = $copiedPath;
        }

        return $installations;
    }

    private function isTempestCompletionScript(string $path): bool
    {
        $content = str(read_file($path));

        return $content->contains('tempest') || $content->contains('_sf_tempest');
    }

    private function removeRcInstallation(Shell $shell): bool
    {
        $rcFile = $shell->rcFile();

        if (! is_file($rcFile)) {
            return true;
        }

        $newContent = read_file($rcFile)
                |> $this->removeCompletionLines(...)
                |> (static fn (string $c): string => preg_replace("/\n{3,}/", "\n\n", $c) ?? $c);

        write_file($rcFile, $newContent);
        $this->info("Removed completion config from {$rcFile}");

        return true;
    }

    private function removeCopiedFile(string $path): bool
    {
        if (! is_file($path)) {
            return true;
        }

        delete_file($path);
        $this->info("Removed completion script: {$path}");

        return true;
    }
}
