<?php

declare(strict_types=1);

namespace Tempest\Console\Completion;

final readonly class CompletionOutputFormatter
{
    public function format(array $completions): string
    {
        $output = [];

        foreach ($completions as $completion) {
            if (! $completion instanceof CompletionCandidate) {
                continue;
            }

            $output[] = $completion->display === null
                ? $completion->value
                : "{$completion->value}\t{$completion->display}";
        }

        return implode("\n", $output);
    }
}
