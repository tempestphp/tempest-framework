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

final readonly class CompletionShowCommand
{
    public function __construct(
        private Console $console,
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
        ?string $shell = null,
    ): ExitCode {
        $shellEnum = $this->resolveShell($shell);

        if ($shellEnum === null) {
            $this->console->error('Could not determine shell. Please specify with --shell=zsh or --shell=bash');

            return ExitCode::ERROR;
        }

        $sourcePath = $this->getSourcePath($shellEnum);

        if (! Filesystem\is_file($sourcePath)) {
            $this->console->error("Completion script not found: {$sourcePath}");

            return ExitCode::ERROR;
        }

        $this->console->writeRaw(Filesystem\read_file($sourcePath));

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
                question: 'Which shell completion script do you want to see?',
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
