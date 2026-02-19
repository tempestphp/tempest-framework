<?php

declare(strict_types=1);

namespace Tempest\Console\Completion;

final readonly class CompletionInvocationInspector
{
    public function isPhpBinary(string $value): bool
    {
        return $this->basename($value) === 'php';
    }

    public function isTempestInvocation(string $value): bool
    {
        return $this->basename($value) === 'tempest';
    }

    private function basename(string $value): string
    {
        $basename = basename($value);

        return $basename === '' ? $value : $basename;
    }
}
