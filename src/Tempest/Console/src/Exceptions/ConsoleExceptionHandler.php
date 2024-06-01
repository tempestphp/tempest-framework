<?php

declare(strict_types=1);

namespace Tempest\Console\Exceptions;

use Tempest\Console\Console;
use Tempest\Console\Input\ConsoleArgumentBag;
use Tempest\ExceptionHandler;
use Tempest\Highlight\Escape;
use Tempest\Highlight\Highlighter;
use Throwable;

final readonly class ConsoleExceptionHandler implements ExceptionHandler
{
    public function __construct(
        private Console $console,
        private Highlighter $highlighter,
        private ConsoleArgumentBag $argumentBag,
    ) {
    }

    public function handle(Throwable $throwable): void
    {
        $this->console
            ->error($throwable::class)
            ->when(
                expression: $throwable->getMessage(),
                callback: fn (Console $console) => $console->error($throwable->getMessage()),
            )
            ->writeln();

        $this->writeSnippet($throwable);

        if ($this->argumentBag->get('-v')) {
            $this->console->writeln();

            foreach ($throwable->getTrace() as $i => $trace) {
                $this->console->writeln("#{$i} " . $this->formatTrace($trace));
            }

            $this->console->writeln();
        } else {
            $this->console
                ->writeln()
                ->writeln('<u>' . $throwable->getFile() . ':' . $throwable->getLine() . '</u>')
                ->writeln();
        }

        ll(exception: $throwable->getMessage());
    }

    private function writeSnippet(Throwable $throwable): void
    {
        $this->console->writeln($this->getCodeSample($throwable));
    }

    private function getCodeSample(Throwable $throwable): string
    {
        $highlighter = $this->highlighter->withGutter();
        $code = Escape::terminal($highlighter->parse(file_get_contents($throwable->getFile()), 'php'));
        $lines = explode(PHP_EOL, $code);

        $lines[$throwable->getLine() - 1] = $lines[$throwable->getLine() - 1] . ' <error><</error>';

        $excerptSize = 5;
        $start = max(0, $throwable->getLine() - $excerptSize);
        $lines = array_slice($lines, $start, $excerptSize * 2);

        return PHP_EOL . implode(PHP_EOL, $lines);
    }

    /**
     * @param mixed $trace
     * @return string
     */
    public function formatTrace(mixed $trace): string
    {
        if (isset($trace['file'])) {
            return '<u>' . $trace['file'] . ':' . $trace['line'] . '</u>';
        }

        if (isset($trace['class'])) {
            return $trace['class'] . $trace['type'] . $trace['function'];
        }

        return $trace['function'] . '()';
    }
}
