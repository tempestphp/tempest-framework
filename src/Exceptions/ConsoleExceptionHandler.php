<?php

declare(strict_types=1);

namespace Tempest\Console\Exceptions;

use Tempest\Console\Console;
use Tempest\Console\ConsoleStyle;
use Tempest\ExceptionHandler;
use Throwable;

final readonly class ConsoleExceptionHandler implements ExceptionHandler
{
    public function __construct(private Console $console)
    {
    }

    public function handle(Throwable $throwable): void
    {
        $this->console->writeln(ConsoleStyle::BOLD(ConsoleStyle::FG_RED($throwable::class)));

        if ($message = $throwable->getMessage()) {
            $this->console->writeln(ConsoleStyle::BOLD(ConsoleStyle::FG_RED($message)));
        }

        $this->console->writeln($throwable->getFile() . ':' . $throwable->getLine());

        foreach ($throwable->getTrace() as $line) {
            //            $this->console->writeln('');

            $this->outputLine($line);
            //            $this->outputPath($line);
        }
    }

    private function outputPath(array $line): void
    {
        if (! isset($line['file'])) {
            return;
        }

        $this->console->write($line['file'] . ':' . $line['line']);
        $this->console->writeln('');
    }

    private function outputLine(array $line): void
    {
        $this->console->write(' - ');
        match (true) {
            isset($line['class']) => $this->outputClassLine($line),
            isset($line['function']) => $this->outputFunctionLine($line),
            default => $this->outputDefaultLine($line),
        };
    }

    private function outputClassLine(array $line): void
    {
        $this->console->write(ConsoleStyle::FG_RED($line['class']));
        $this->console->write($line['type']);
        $this->console->write(ConsoleStyle::FG_DARK_GREEN($line['function']));
        $this->console->writeln('');
    }

    private function outputFunctionLine(array $line): void
    {
        $this->console->write(ConsoleStyle::FG_DARK_GREEN($line['function']));
        $this->console->writeln('');
    }

    private function outputDefaultLine(array $line): void
    {
        $this->console->write($line['file'] . ':' . $line['line']);
        $this->console->writeln('');
    }
}
