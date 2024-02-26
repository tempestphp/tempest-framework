<?php

declare(strict_types=1);

namespace Tempest\Exceptions;

use Tempest\Console\Console;
use Tempest\Console\ConsoleStyle;
use Throwable;

final readonly class ConsoleExceptionHandler implements ExceptionHandler
{
    public function __construct(private Console $console)
    {
    }

    public function handle(Throwable $throwable): void
    {
        $this->console->writeln($throwable::class, ConsoleStyle::FG_RED, ConsoleStyle::BOLD);

        if ($message = $throwable->getMessage()) {
            $this->console->writeln($message, ConsoleStyle::FG_RED, ConsoleStyle::BOLD);
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
        $this->console->write($line['class'], ConsoleStyle::FG_RED);
        $this->console->write($line['type']);
        $this->console->write($line['function'], ConsoleStyle::FG_DARK_GREEN);
        $this->formatArguments($line['args']);
        $this->console->writeln('');
    }

    private function outputFunctionLine(array $line): void
    {
        $this->console->write($line['function'], ConsoleStyle::FG_DARK_GREEN);
        $this->formatArguments($line['args']);
        $this->console->writeln('');
    }

    private function outputDefaultLine(array $line): void
    {
        $this->console->write($line['file'] . ':' . $line['line']);
        $this->console->writeln('');
    }

    private function formatArguments(mixed $args): void
    {
        return;
        $args = implode(
            separator: ', ',
            array: array_map(
                callback: fn ($arg) => str_replace(PHP_EOL, '', var_export($arg, true)),
                array: $args,
            ),
        );

        $this->console->write('(');
        $this->console->write($args);
        $this->console->write(')');
    }
}
