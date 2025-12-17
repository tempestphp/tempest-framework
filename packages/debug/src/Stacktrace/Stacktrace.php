<?php

declare(strict_types=1);

namespace Tempest\Debug\Stacktrace;

use Throwable;

use function Tempest\Support\Path\to_relative_path;

final class Stacktrace
{
    /**
     * @param array<int, Frame> $frames
     */
    public array $applicationFrames {
        get => array_values(array_filter(
            array: $this->frames,
            callback: fn (Frame $frame) => ! $frame->isVendor,
        ));
    }

    /**
     * @param array<int, Frame> $frames
     */
    public array $vendorFrames {
        get => array_values(array_filter(
            array: $this->frames,
            callback: fn (Frame $frame) => $frame->isVendor,
        ));
    }

    /**
     * @param array<int, Frame> $frames
     */
    public function __construct(
        private(set) string $message,
        private(set) string $exceptionClass,
        private(set) array $frames,
        private(set) string $file,
        private(set) int $line,
        private(set) string $absoluteFile,
        private(set) string $relativeFile,
    ) {}

    public static function fromThrowable(Throwable $throwable, int $contextLines = 5, ?string $rootPath = null): self
    {
        $frames = [];
        $trace = $throwable->getTrace();
        $firstTraceFrame = $trace[0] ?? null;
        $snippet = null;

        $exceptionFile = $throwable->getFile();
        $exceptionLine = $throwable->getLine();
        $isVendor = Frame::isVendorFile($exceptionFile, $rootPath);

        if ($exceptionFile && $exceptionLine && ! $isVendor && file_exists($exceptionFile)) {
            $snippet = Frame::extractCodeSnippet($exceptionFile, $exceptionLine, $contextLines);
        }

        $absoluteExceptionFile = $exceptionFile;
        $relativeExceptionFile = $rootPath ? to_relative_path($rootPath, $exceptionFile) : $exceptionFile;
        $arguments = $firstTraceFrame ? Frame::extractArguments($firstTraceFrame) : [];

        $frames[] = new Frame(
            file: $exceptionFile,
            line: $exceptionLine,
            class: $firstTraceFrame['class'] ?? null,
            function: $firstTraceFrame['function'] ?? null,
            type: $firstTraceFrame['type'] ?? null,
            isVendor: $isVendor,
            snippet: $snippet,
            absoluteFile: $absoluteExceptionFile,
            relativeFile: $relativeExceptionFile,
            arguments: $arguments,
            index: 1,
        );

        foreach (array_slice($trace, 1) as $i => $frame) {
            $frames[] = Frame::fromArray($frame, $contextLines, $rootPath, $i + 2);
        }

        $absoluteFile = $throwable->getFile();
        $relativeFile = $rootPath ? to_relative_path($rootPath, $absoluteFile) : $absoluteFile;

        return new self(
            message: $throwable->getMessage(),
            exceptionClass: $throwable::class,
            frames: $frames,
            file: $throwable->getFile(),
            line: $throwable->getLine(),
            absoluteFile: $absoluteFile,
            relativeFile: $relativeFile,
        );
    }
}
