<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use Tempest\Console\Actions\BuildCompletionMetadata;
use Tempest\Console\CompletionRuntime;
use Tempest\Console\Console;
use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ExitCode;
use Tempest\Support\Filesystem;

final readonly class CompletionGenerateCommand
{
    public function __construct(
        private Console $console,
        private CompletionRuntime $completionRuntime,
        private BuildCompletionMetadata $buildCompletionMetadata,
    ) {}

    #[ConsoleCommand(
        name: 'completion:generate',
        description: 'Generate shell completion metadata as JSON',
    )]
    public function __invoke(
        #[ConsoleArgument(
            description: 'Optional output path for the completion metadata JSON',
            aliases: ['-p'],
        )]
        ?string $path = null,
    ): ExitCode {
        if (! $this->completionRuntime->isSupportedPlatform()) {
            $this->console->error($this->completionRuntime->getUnsupportedPlatformMessage());

            return ExitCode::ERROR;
        }

        $path ??= $this->completionRuntime->getMetadataPath();

        Filesystem\write_json($path, ($this->buildCompletionMetadata)(), pretty: false);

        $this->console->success("Wrote completion metadata to: {$path}");

        return ExitCode::SUCCESS;
    }
}
