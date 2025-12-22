<?php

declare(strict_types=1);

namespace Tempest\Debug\Stacktrace;

use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;

use function Tempest\Support\Path\to_relative_path;

final class Frame
{
    /**
     * @param array<Argument> $arguments
     */
    public function __construct(
        private(set) int $line,
        private(set) ?string $class,
        private(set) ?string $function,
        private(set) ?string $type,
        private(set) bool $isVendor,
        private(set) ?CodeSnippet $snippet,
        private(set) string $absoluteFile,
        private(set) string $relativeFile,
        private(set) array $arguments,
        private(set) int $index,
    ) {}

    public static function fromArray(array $frame, int $contextLines = 5, ?string $rootPath = null, int $index = 1): self
    {
        $absoluteFile = $frame['file'] ?? '';
        $line = $frame['line'] ?? 0;
        $isVendor = self::isVendorFile($absoluteFile, $rootPath);
        $snippet = null;

        if ($absoluteFile && $line && ! $isVendor && file_exists($absoluteFile)) {
            $snippet = self::extractCodeSnippet($absoluteFile, $line, $contextLines);
        }

        return new self(
            line: $line,
            class: $frame['class'] ?? null,
            function: $frame['function'] ?? null,
            type: $frame['type'] ?? null,
            isVendor: $isVendor,
            snippet: $snippet,
            absoluteFile: $absoluteFile,
            relativeFile: $rootPath ? to_relative_path($rootPath, $absoluteFile) : $absoluteFile,
            arguments: self::extractArguments($frame),
            index: $index,
        );
    }

    /**
     * @return array<Argument>
     */
    public static function extractArguments(array $frame): array
    {
        if (! isset($frame['args']) || ! is_array($frame['args'])) {
            return [];
        }

        $arguments = $frame['args'];
        $parameterNames = [];

        try {
            $reflection = isset($frame['class'], $frame['function'])
                ? new ReflectionMethod(objectOrMethod: $frame['class'], method: $frame['function'])
                : new ReflectionFunction(function: $frame['function']);

            $parameterNames = array_map(
                callback: fn (ReflectionParameter $param) => $param->getName(),
                array: $reflection->getParameters(),
            );
        } catch (\Throwable) {
            // @mago-expect lint:no-empty-catch-clause
        }

        $result = [];
        foreach ($arguments as $index => $value) {
            $result[] = Argument::make(
                name: $parameterNames[$index] ?? $index,
                value: $value,
            );
        }

        return $result;
    }

    public static function isVendorFile(string $file, ?string $rootPath = null): bool
    {
        if ($file === '') {
            return false;
        }

        if ($rootPath !== null) {
            return ! str_starts_with(
                haystack: str_replace('\\', '/', $file),
                needle: str_replace('\\', '/', $rootPath),
            );
        }

        return str_contains($file, '/vendor/') || str_contains($file, '\\vendor\\');
    }

    public static function extractCodeSnippet(string $file, int $line, int $contextLines): ?CodeSnippet
    {
        $fileLines = file($file, FILE_IGNORE_NEW_LINES);

        if ($fileLines === false) {
            return null;
        }

        $startLine = max(1, $line - $contextLines);
        $endLine = min(count($fileLines), $line + $contextLines);
        $lines = [];

        for ($i = $startLine; $i <= $endLine; $i++) {
            $lines[$i] = $fileLines[$i - 1];
        }

        if ($lines === []) {
            return null;
        }

        return new CodeSnippet(
            lines: $lines,
            highlightedLine: $line,
        );
    }

    public function getMethodName(): string
    {
        if (! $this->class) {
            return $this->function ?? '';
        }

        $type = match ($this->type) {
            '::' => '::',
            '->' => '->',
            default => '',
        };

        return $this->class . $type . ($this->function ?? '');
    }
}
