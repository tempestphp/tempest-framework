<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use RuntimeException;
use Tempest\Console\Actions\EnsureCompletionHelperBinary;
use Tempest\Console\CompletionRuntime;
use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ExitCode;

final readonly class CompletionUpdateBinCommand
{
    public function __construct(
        private Console $console,
        private EnsureCompletionHelperBinary $ensureCompletionHelperBinary,
    ) {}

    #[ConsoleCommand(
        name: 'completion:update-bin',
        description: 'Update the completion helper binary',
    )]
    public function __invoke(): ExitCode
    {
        if (! CompletionRuntime::isSupportedPlatform()) {
            $this->console->error(CompletionRuntime::getUnsupportedPlatformMessage());

            return ExitCode::ERROR;
        }

        try {
            $binaryPath = ($this->ensureCompletionHelperBinary)(update: true);
        } catch (RuntimeException $runtimeException) {
            $this->console->error($runtimeException->getMessage());

            return ExitCode::ERROR;
        }

        $this->console->success("Updated completion helper binary: {$binaryPath}");

        return ExitCode::SUCCESS;
    }
}
