<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use Symfony\Component\Filesystem\Path;
use Tempest\Console\Actions\BuildCompletionMetadata;
use Tempest\Console\Actions\ResolveShell;
use Tempest\Console\CompletionRuntime;
use Tempest\Console\Console;
use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\Enums\Shell;
use Tempest\Console\ExitCode;
use Tempest\Console\Middleware\ForceMiddleware;
use Tempest\Support\Filesystem;

use function Tempest\Support\path;

final readonly class CompletionInstallCommand
{
    public function __construct(
        private Console $console,
        private CompletionRuntime $completionRuntime,
        private ResolveShell $resolveShell,
        private BuildCompletionMetadata $buildCompletionMetadata,
    ) {}

    #[ConsoleCommand(
        name: 'completion:install',
        description: 'Install shell completion for Tempest',
        middleware: [ForceMiddleware::class],
    )]
    public function __invoke(
        #[ConsoleArgument(
            description: 'The shell to install completions for (zsh, bash)',
            aliases: ['-s'],
        )]
        ?Shell $shell = null,
    ): ExitCode {
        if (! $this->completionRuntime->isSupportedPlatform()) {
            $this->console->error($this->completionRuntime->getUnsupportedPlatformMessage());

            return ExitCode::ERROR;
        }

        $shell ??= ($this->resolveShell)('Which shell do you want to install completions for?');

        if ($shell === null) {
            $this->console->error('Could not detect shell. Please specify one using the --shell option. Possible values are: zsh, bash.');

            return ExitCode::ERROR;
        }

        $sourcePath = $this->getSourcePath($shell);
        $targetDir = $this->completionRuntime->getInstallationDirectory();
        $targetPath = $this->completionRuntime->getInstalledCompletionPath($shell);

        if (! Filesystem\is_file($sourcePath)) {
            $this->console->error("Completion script not found: {$sourcePath}");

            return ExitCode::ERROR;
        }

        if (! $this->console->isForced) {
            $this->console->info("Installing {$shell->value} completions");
            $this->console->keyValue('Source', $sourcePath);
            $this->console->keyValue('Target', $targetPath);
            $this->console->writeln();

            if (! $this->console->confirm('Proceed with installation?', default: true)) {
                $this->console->warning('Installation cancelled.');

                return ExitCode::CANCELLED;
            }
        }

        Filesystem\write_json($this->completionRuntime->getMetadataPath(), ($this->buildCompletionMetadata)(), pretty: false);

        Filesystem\ensure_directory_exists($targetDir);

        if (Filesystem\is_file($targetPath) && ! $this->console->confirm('Completion file already exists. Overwrite?', default: true)) {
            $this->console->warning('Installation cancelled.');

            return ExitCode::CANCELLED;
        }

        $script = Filesystem\read_file($sourcePath);
        Filesystem\write_file($targetPath, $script);

        $this->console->success("Installed completion script to: {$targetPath}");

        $this->console->writeln();
        $this->console->info('Next steps:');
        $this->console->instructions($this->completionRuntime->getPostInstallInstructions($shell));

        return ExitCode::SUCCESS;
    }

    private function getSourcePath(Shell $shell): string
    {
        return Path::canonicalize(
            path(__DIR__, '..', $shell->getSourceFilename())->toString(),
        );
    }
}
