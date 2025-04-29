<?php

declare(strict_types=1);

namespace Tempest\Console\Exceptions;

use Tempest\Console\Console;
use Tempest\Console\ExitCode;
use Tempest\Console\HasExitCode;
use Tempest\Console\Input\ConsoleArgumentBag;
use Tempest\Container\Tag;
use Tempest\Core\ErrorHandler;
use Tempest\Core\Kernel;
use Tempest\Highlight\Escape;
use Tempest\Highlight\Highlighter;
use Throwable;

use function Tempest\Support\str;

final readonly class ConsoleErrorHandler implements ErrorHandler
{
    public function __construct(
        private Kernel $kernel,
        #[Tag('console')]
        private Highlighter $highlighter,
        private Console $console,
        private ConsoleArgumentBag $argumentBag,
    ) {}

    public function handleException(Throwable $throwable): void
    {
        ll(exception: $throwable->getMessage());

        $this->console
            ->writeln()
            ->error($throwable::class)
            ->when(
                condition: $throwable->getMessage(),
                callback: fn (Console $console) => $console->error($throwable->getMessage()),
            )
            ->writeln()
            ->writeln('In ' . $this->formatFileWithLine($throwable->getFile() . ':' . $throwable->getLine()))
            ->writeln($this->getSnippet($throwable->getFile(), $throwable->getLine()))
            ->writeln();

        if ($this->argumentBag->get('-v') !== null) {
            foreach ($throwable->getTrace() as $i => $trace) {
                $this->console->writeln("<style='bold fg-blue'>#{$i}</style> " . $this->formatTrace($trace));
            }

            $this->console->writeln();
        } else {
            $this->console
                ->writeln('<style="fg-blue bold">#0</style> ' . $this->formatTrace($throwable->getTrace()[0]))
                ->writeln('<style="fg-blue bold">#1</style> ' . $this->formatTrace($throwable->getTrace()[1]))
                ->writeln()
                ->writeln('   <style="dim">Run with -v to show more.</style>')
                ->writeln();
        }

        $exitCode = ($throwable instanceof HasExitCode) ? $throwable->getExitCode() : ExitCode::ERROR;

        $this->kernel->shutdown($exitCode->value);
    }

    public function handleError(int $errNo, string $errstr, string $errFile, int $errLine): void
    {
        ll(error: $errstr);

        $this->console
            ->writeln()
            ->error($errstr)
            ->writeln($this->getSnippet($errFile, $errLine));
    }

    private function getSnippet(string $file, int $lineNumber): string
    {
        $highlighter = $this->highlighter->withGutter();
        $code = Escape::terminal($highlighter->parse(file_get_contents($file), language: 'php'));
        $lines = explode(PHP_EOL, $code);

        $lines[$lineNumber - 1] = str($lines[$lineNumber - 1])
            ->replaceRegex('/^\d+/', fn (array $match) => "<style='fg-red'>{$match[0]}</style>")
            ->append('  <style="fg-red"><<<</style>')
            ->toString();

        $excerptSize = 5;
        $start = max(0, ($lineNumber - $excerptSize) - 2);
        $lines = array_slice($lines, $start, $excerptSize * 2);

        return PHP_EOL . implode(PHP_EOL, $lines);
    }

    private function formatFileWithLine(string $file): string
    {
        [$file, $line] = explode(':', $file);
        $directory = dirname($file);
        $filename = basename($file);

        return sprintf('<style="fg-gray">%s/</style>%s<style="dim">:</style>%s', $directory, $filename, $line);
    }

    private function formatTrace(array $trace): string
    {
        if (isset($trace['file'])) {
            return $this->formatFileWithLine($trace['file'] . ':' . $trace['line']);
        }

        if (isset($trace['class'])) {
            return sprintf(
                "%s<style='dim'>%s</style>%s<style='dim'>()</style>",
                $trace['class'],
                $trace['type'],
                $trace['function'],
            );
        }

        return sprintf("%s<style='dim'>()</style>", $trace['function']);
    }
}
