<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use RuntimeException;
use Tempest\Console\Actions\BuildCompletionMetadata;
use Tempest\Console\Actions\EnsureCompletionHelperBinary;
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
        private BuildCompletionMetadata $buildCompletionMetadata,
        private EnsureCompletionHelperBinary $ensureCompletionHelperBinary,
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
        if (! CompletionRuntime::isSupportedPlatform()) {
            $this->console->error(CompletionRuntime::getUnsupportedPlatformMessage());

            return ExitCode::ERROR;
        }

        try {
            ($this->ensureCompletionHelperBinary)();
        } catch (RuntimeException $runtimeException) {
            $this->console->error($runtimeException->getMessage());

            return ExitCode::ERROR;
        }

        $path ??= CompletionRuntime::getMetadataPath();

        Filesystem\write_json($path, ($this->buildCompletionMetadata)(), pretty: false);

        $this->console->success("Wrote completion metadata to: {$path}");

        return ExitCode::SUCCESS;
    }
}
