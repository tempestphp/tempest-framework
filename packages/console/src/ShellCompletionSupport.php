<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\Console\Enums\Shell;

use function Tempest\Support\str;

trait ShellCompletionSupport
{
    private const string COMPLETION_MARKER = '# Tempest shell completion';

    private function resolveShell(?string $shell): ?Shell
    {
        return $shell !== null ? Shell::tryFrom($shell) : Shell::detect();
    }

    private function getCompletionScriptPath(Shell $shell): string
    {
        return __DIR__ . '/' . $shell->completionSourceFile();
    }

    private function removeCompletionLines(string $content): string
    {
        $lines = explode(PHP_EOL, $content);
        $result = [];
        $inCompletionBlock = false;

        foreach ($lines as $line) {
            if (str($line)->contains(self::COMPLETION_MARKER)) {
                $inCompletionBlock = true;

                continue;
            }

            if ($inCompletionBlock) {
                $trimmed = str($line)->trim();

                if ($trimmed->startsWith('source') || $trimmed->startsWith('fpath=') || $trimmed->startsWith('autoload')) {
                    continue;
                }

                if ($trimmed->isEmpty()) {
                    $inCompletionBlock = false;

                    continue;
                }

                $inCompletionBlock = false;
            }

            $result[] = $line;
        }

        return implode(PHP_EOL, $result);
    }
}
