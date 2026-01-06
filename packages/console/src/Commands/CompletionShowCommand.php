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

final readonly class CompletionShowCommand
{
    public function __construct(
        private Console $console,
        private ResolveShell $resolveShell,
    ) {}

    #[ConsoleCommand(
        name: 'completion:show',
        description: 'Output the shell completion script to stdout',
    )]
    public function __invoke(
        #[ConsoleArgument(
            description: 'The shell to show completions for (zsh, bash)',
            aliases: ['-s'],
        )]
        ?Shell $shell = null,
    ): ExitCode {
        $shell ??= ($this->resolveShell)('Which shell completion script do you want to see?');

        if ($shell === null) {
            $this->console->error('Could not detect shell. Please specify one using the --shell option. Possible values are: zsh, bash.');

            return ExitCode::ERROR;
        }

        $sourcePath = $this->getSourcePath($shell);

        if (! Filesystem\is_file($sourcePath)) {
            $this->console->error("Completion script not found: {$sourcePath}");

            return ExitCode::ERROR;
        }

        $this->console->writeRaw(Filesystem\read_file($sourcePath));

        return ExitCode::SUCCESS;
    }

    private function getSourcePath(Shell $shell): string
    {
        return Path::canonicalize(
            path(__DIR__, '..', $shell->getSourceFilename())->toString(),
        );
    }
}
