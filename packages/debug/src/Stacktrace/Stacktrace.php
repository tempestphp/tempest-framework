<?php

declare(strict_types=1);

namespace Tempest\Debug\Stacktrace;

use Throwable;

use function Tempest\Support\Path\to_relative_path;

final class Stacktrace
{
    /** @var array<Frame> */
    public array $applicationFrames {
        get => array_values(array_filter(
            array: $this->frames,
            callback: fn (Frame $frame) => ! $frame->isVendor,
        ));
    }

    /** @var array<int,Frame> */
    public array $vendorFrames {
        get => array_values(array_filter(
            array: $this->frames,
            callback: fn (Frame $frame) => $frame->isVendor,
        ));
    }

    /**
     * @param array<int,Frame> $frames
     */
    public function __construct(
        private(set) string $message,
        private(set) string $exceptionClass,
        private(set) array $frames,
        private(set) int $line,
        private(set) string $absoluteFile,
        private(set) string $relativeFile,
    ) {}

    public static function fromThrowable(Throwable $throwable, int $contextLines = 5, ?string $rootPath = null): self
    {
        $frames = [];
        $snippet = null;
        $trace = $throwable->getTrace();
        $firstTraceFrame = $trace[0] ?? null;
        $exceptionFile = $throwable->getFile();
        $exceptionLine = $throwable->getLine();
        $isVendor = Frame::isVendorFile($exceptionFile, $rootPath);

        if ($exceptionFile && $exceptionLine && ! $isVendor && file_exists($exceptionFile)) {
            $snippet = Frame::extractCodeSnippet($exceptionFile, $exceptionLine, $contextLines);
        }

        $frames[] = new Frame(
            line: $exceptionLine,
            class: $firstTraceFrame['class'] ?? null,
            function: $firstTraceFrame['function'] ?? null,
            type: $firstTraceFrame['type'] ?? null,
            isVendor: $isVendor,
            snippet: $snippet,
            absoluteFile: $exceptionFile,
            relativeFile: $rootPath
                ? to_relative_path($rootPath, $exceptionFile)
                : $exceptionFile,
            arguments: $firstTraceFrame
                ? Frame::extractArguments($firstTraceFrame)
                : [],
            index: 1,
        );

        foreach (array_slice($trace, offset: 1) as $i => $frame) {
            $frames[] = Frame::fromArray($frame, $contextLines, $rootPath, $i + 2);
        }

        return new self(
            message: $throwable->getMessage(),
            exceptionClass: $throwable::class,
            frames: $frames,
            line: $throwable->getLine(),
            absoluteFile: $exceptionFile,
            relativeFile: $rootPath ? to_relative_path($rootPath, $exceptionFile) : $exceptionFile,
        );
    }

    public function prependFrame(Frame $frame): self
    {
        return new self(
            message: $this->message,
            exceptionClass: $this->exceptionClass,
            frames: [
                // we add our frame
                new Frame(
                    line: $frame->line,
                    class: $frame->class,
                    function: $frame->function,
                    type: $frame->type,
                    isVendor: $frame->isVendor,
                    snippet: $frame->snippet,
                    absoluteFile: $frame->absoluteFile,
                    relativeFile: $frame->relativeFile,
                    arguments: $frame->arguments,
                    index: 1,
                ),
                // and shift the frame index by one for each frame
                ...array_map(fn (Frame $frame) => new Frame(
                    line: $frame->line,
                    class: $frame->class,
                    function: $frame->function,
                    type: $frame->type,
                    isVendor: $frame->isVendor,
                    snippet: $frame->snippet,
                    absoluteFile: $frame->absoluteFile,
                    relativeFile: $frame->relativeFile,
                    arguments: $frame->arguments,
                    index: $frame->index + 1,
                ), $this->frames),
            ],
            line: $this->line,
            absoluteFile: $this->absoluteFile,
            relativeFile: $this->relativeFile,
        );
    }
}
