<?php

declare(strict_types=1);

namespace Tempest\Console\Actions;

use Tempest\Console\CompletionRuntime;

final readonly class RenderCompletionScript
{
    public function __invoke(string $script): string
    {
        return str_replace(
            [
                CompletionRuntime::HELPER_PATH_PLACEHOLDER,
                CompletionRuntime::METADATA_PATH_PLACEHOLDER,
            ],
            [
                CompletionRuntime::getHelperBinaryPath(),
                CompletionRuntime::getMetadataPath(),
            ],
            $script,
        );
    }
}
