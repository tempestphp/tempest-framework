<?php

declare(strict_types=1);

namespace Tempest\Console\Completion;

final readonly class CompletionInputNormalizer
{
    public function __construct(
        private CompletionInvocationInspector $invocationInspector = new CompletionInvocationInspector(),
    ) {}

    public function normalize(array $words, int $currentIndex): ?CompletionInput
    {
        if ($words === []) {
            return null;
        }

        $normalizedWords = $this->normalizeWords($words);

        if ($this->invocationInspector->isPhpBinary($normalizedWords[0])) {
            if (count($normalizedWords) < 2 || ! $this->invocationInspector->isTempestInvocation($normalizedWords[1])) {
                return null;
            }

            array_shift($normalizedWords);
            $currentIndex = max(0, $currentIndex - 1);
        }

        if ($normalizedWords === [] || ! $this->invocationInspector->isTempestInvocation($normalizedWords[0])) {
            return null;
        }

        if ($currentIndex >= count($normalizedWords)) {
            $normalizedWords[] = '';
        }

        $currentIndex = min($currentIndex, count($normalizedWords) - 1);

        return new CompletionInput(
            words: $normalizedWords,
            currentIndex: $currentIndex,
        );
    }

    private function normalizeWords(array $words): array
    {
        $normalizedWords = [];

        foreach ($words as $word) {
            $normalizedWords[] = is_string($word) ? $word : '';
        }

        return $normalizedWords;
    }
}
