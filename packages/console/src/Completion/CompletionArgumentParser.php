<?php

declare(strict_types=1);

namespace Tempest\Console\Completion;

final readonly class CompletionArgumentParser
{
    public function parse(array $args): ?CompletionArguments
    {
        $metadataPath = $args[0] ?? null;
        $currentIndex = $args[1] ?? null;

        if (! is_string($metadataPath) || ! is_string($currentIndex) || $currentIndex === '' || ctype_digit($currentIndex) === false) {
            return null;
        }

        return new CompletionArguments(
            metadataPath: $metadataPath,
            currentIndex: (int) $currentIndex,
            words: array_slice($args, 2),
        );
    }
}
