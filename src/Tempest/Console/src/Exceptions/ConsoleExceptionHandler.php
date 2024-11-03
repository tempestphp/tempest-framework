<?php

declare(strict_types=1);

namespace Tempest\Console\Exceptions;

use Tempest\Console\Console;
use Tempest\Console\Input\ConsoleArgumentBag;
use Tempest\Container\Tag;
use Tempest\Core\ExceptionHandler;
use Tempest\Highlight\Escape;
use Tempest\Highlight\Highlighter;
use Throwable;

final readonly class ConsoleExceptionHandler implements ExceptionHandler
{
    public function __construct(
        #[Tag('console')]
        private Highlighter $highlighter,
        private Console $console,
        private ConsoleArgumentBag $argumentBag,
    ) {
    }

    public function handleException(Throwable $throwable): void
    {
        ll(exception: $throwable->getMessage());

        $this->console
            ->writeln()
            ->error($throwable::class)
            ->when(
                expression: $throwable->getMessage(),
                callback: fn (Console $console) => $console->error($throwable->getMessage()),
            )
            ->writeln($this->getSnippet($throwable->getFile(), $throwable->getLine()))
            ->writeln()
            ->writeln('<u>' . $throwable->getFile() . ':' . $throwable->getLine() . '</u>')
            ->writeln();

        if ($this->argumentBag->get('-v') !== null) {
            foreach ($throwable->getTrace() as $i => $trace) {
                $this->console->writeln("<h2>#{$i}</h2> " . $this->formatTrace($trace));
            }

            $this->console->writeln();
        } else {
            $firstLine = $throwable->getTrace()[0];

            $this->console
                ->writeln("<h2>#0</h2> " . $this->formatTrace($firstLine))
                ->writeln()
                ->writeln('<em>-v</em> show more')
                ->writeln();
        }
    }

    public function handleError(int $errNo, string $errstr, string $errFile, int $errLine): void
    {
        ll('error');

        $this->console
            ->writeln()
            ->error($errstr)
            ->writeln($this->getSnippet($errFile, $errLine));
    }

    private function getSnippet(string $file, int $lineNumber): string
    {
        $highlighter = $this->highlighter->withGutter();
        $code = Escape::terminal($highlighter->parse(file_get_contents($file), 'php'));
        $lines = explode(PHP_EOL, $code);

        $lines[$lineNumber - 1] = $lines[$lineNumber - 1] . ' <error><</error>';

        $excerptSize = 5;
        $start = max(0, $lineNumber - $excerptSize - 2);
        $lines = array_slice($lines, $start, $excerptSize * 2);

        return PHP_EOL . implode(PHP_EOL, $lines);
    }

    private function formatTrace(mixed $trace): string
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
