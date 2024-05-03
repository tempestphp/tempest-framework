<?php

declare(strict_types=1);

namespace Tempest\Console;

use Closure;
use Exception;
use Tempest\AppConfig;
use Tempest\Console\Highlight\TempestConsoleLanguage;
use Tempest\Highlight\Highlighter;
use Tempest\Support\PathHelper;

final class BackgroundTaskConsole implements Console
{
    private ?string $label = null;

    public function __construct(
        private readonly Highlighter $highlighter,
        private readonly ConsoleConfig $consoleConfig,
        private readonly AppConfig $appConfig,
    ) {
    }

    public function info(string $line): Console
    {
        $this->writeln('[info] ' . $line);

        return $this;
    }

    public function error(string $line): Console
    {
        $this->writeln('[error] ' . $line);

        return $this;
    }

    public function success(string $line): Console
    {
        $this->writeln('[success] ' . $line);

        return $this;
    }

    public function when(mixed $expression, callable $callback): Console
    {
        if ($expression) {
            $callback($this);
        }

        return $this;
    }

    public function write(string $contents): static
    {
        $this->writeToFile($contents);

        return $this;
    }

    public function writeln(string $line = ''): static
    {
        $this->writeToFile($line . PHP_EOL);

        return $this;
    }

    public function withLabel(string $label): self
    {
        $clone = clone $this;

        $clone->label = $label;

        return $clone;
    }

    public function component(ConsoleComponent $component, array $validation = []): mixed
    {
        throw new Exception("Cannot read input within background tasks");
    }

    public function ask(string $question, ?array $options = null, bool $multiple = false, array $validation = []): string|array
    {
        throw new Exception("Cannot read input within background tasks");
    }

    public function confirm(string $question, bool $default = false): bool
    {
        throw new Exception("Cannot read input within background tasks");
    }

    public function password(string $label = 'Password', bool $confirm = false): string
    {
        throw new Exception("Cannot read input within background tasks");
    }

    public function progressBar(iterable $data, Closure $handler): array
    {
        throw new Exception("Cannot read input within background tasks");
    }

    public function search(string $label, Closure $search): mixed
    {
        throw new Exception("Cannot read input within background tasks");
    }

    public function readln(): string
    {
        throw new Exception("Cannot read input within background tasks");
    }

    public function read(int $bytes): string
    {
        throw new Exception("Cannot read input within background tasks");
    }

    private function writeToFile(string $contents): void
    {
        if ($this->label) {
            $contents = "<h2>{$this->label}</h2> {$contents}";
        }

        $path = $this->consoleConfig->logPath ?? PathHelper::make($this->appConfig->root, 'console.log');

        $handle = fopen($path, 'w');

        fwrite(
            $handle,
            $this->highlighter->parse($contents, new TempestConsoleLanguage()),
        );

        fclose($handle);
    }
}
