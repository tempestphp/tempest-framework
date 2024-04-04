<?php

declare(strict_types=1);

namespace Tempest\Console;

use Stringable;
use Tempest\Support\ArrayHelper;
use Tempest\Support\Conditions\HasConditions;

final class ConsoleOutputBuilder implements Stringable
{
    use HasConditions;

    /** @var ConsoleOutputLine[] */
    private array $lines = [];
    private string $glue = PHP_EOL;

    public function __construct(
        private ConsoleOutput $output,
    ) {

    }

    public function nest(callable $callback): self
    {
        $clone = new self($this->output);

        $callback($clone);

        $this->add($clone->toString());

        return $this;
    }

    public function glueWith(string $glue): self
    {
        $this->glue = $glue;

        return $this;
    }

    /**
     * @param string|string[] $lines
     * @param ConsoleOutputType $type
     *
     * @return $this
     */
    public function add(string|array $lines, ConsoleOutputType $type = ConsoleOutputType::Formatted): self
    {
        $lines = ArrayHelper::wrap($lines);

        foreach ($lines as $key => $line) {
            $lines[$key] = new ConsoleOutputLine($line, $type);
        }

        $this->lines = array_merge($this->lines, $lines);

        return $this;
    }

    public function blank(): self
    {
        return $this->add('', ConsoleOutputType::Brand);
    }

    public function error(string $line): self
    {
        return $this->add($line, ConsoleOutputType::Error);
    }

    public function warning(string $line): self
    {
        return $this->add($line, ConsoleOutputType::Warning);
    }

    public function success(string $line): self
    {
        return $this->add($line, ConsoleOutputType::Success);
    }

    public function info(string $line): self
    {
        return $this->add($line, ConsoleOutputType::Info);
    }

    public function comment(string $line): self
    {
        return $this->add($line, ConsoleOutputType::Comment);
    }

    public function muted(string $line): self
    {
        return $this->add($line, ConsoleOutputType::Muted);
    }

    public function brand(string $group): self
    {
        return $this->add($group, ConsoleOutputType::Brand);
    }

    public function raw(string $message): self
    {
        return $this->add($message);
    }

    public function label(string $message): self
    {
        return $this->add($message, ConsoleOutputType::Label);
    }

    /**
     * @param string[] $lines
     *
     * @return $this
     */
    public function comments(array $lines): self
    {
        if (! $lines) {
            return $this;
        }

        $this->comment("/**");

        foreach ($lines as $line) {
            $this->comment('* ' . $line);
        }

        $this->comment("*/");

        return $this;
    }

    public function header(string $message): self
    {
        return $this->blank()
            ->brand($message)
            ->blank();
    }

    public function write(?ConsoleOutput $to = null): self
    {
        ($to ?? $this->output)->write(
            $this->toString()
        );

        $this->lines = [];

        return $this;
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(bool $format = true): string
    {
        $formattedLines = array_map(
            fn (ConsoleOutputLine $line) => $format ? $line->format() : $line->line,
            $this->lines,
        );

        return implode(
            $this->glue,
            $formattedLines,
        );
    }

    /**
     * @return ConsoleOutputLine[]
     */
    public function getLines(): array
    {
        return $this->lines;
    }
}
