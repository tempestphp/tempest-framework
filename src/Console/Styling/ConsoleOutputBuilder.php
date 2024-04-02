<?php

declare(strict_types=1);

namespace Tempest\Console\Styling;

use Stringable;
use function Tempest\get;
use Tempest\Support\ArrayHelper;
use Tempest\Support\BaseBuilder;

final class ConsoleOutputBuilder extends BaseBuilder implements Stringable
{
    public function __construct(
        /** @var OutputLine[] */
        protected array $lines = [],
        protected string $glue = PHP_EOL,
    ) {
    }

    public static function new(string $glue = PHP_EOL): ConsoleOutputBuilder
    {
        return new self(
            [],
            $glue,
        );
    }

    public function blank(): self
    {
        return $this->add('', LineType::Brand);
    }

    public function error(string $line): self
    {
        return $this->add($line, LineType::Error);
    }

    public function warning(string $line): self
    {
        return $this->add($line, LineType::Warning);
    }

    public function success(string $line): self
    {
        return $this->add($line, LineType::Success);
    }

    public function info(string $line): self
    {
        return $this->add($line, LineType::Info);
    }

    public function comment(string $line): self
    {
        return $this->add($line, LineType::Comment);
    }

    public function comments(array $lines): self
    {
        if (! $lines) {
            return $this;
        }

        $this->comment("/**");

        foreach ($lines as $line) {
            $this->comment('* ' . $line);
        }

        $this->comment("*/")
            ->blank();

        return $this;
    }

    public function muted(string $line): self
    {
        return $this->add($line, LineType::Muted);
    }

    /**
     * @param string|string[] $lines
     * @param LineType $type
     *
     * @return $this
     */
    public function add(string|array $lines, LineType $type): self
    {
        $lines = ArrayHelper::wrap($lines);

        foreach ($lines as $key => $line) {
            $lines[$key] = new OutputLine($line, $type);
        }

        $this->lines = array_merge($this->lines, $lines);

        return $this;
    }

    public function __toString(): string
    {
        $theme = get(ConsoleOutputTheme::class);

        $formattedLines = array_map(
            fn (OutputLine $el) => $el->format($theme),
            $this->lines
        );

        return implode(
            $this->glue,
            $formattedLines,
        );
    }

    public function toString(): string
    {
        return $this->__toString();
    }

    public function brand(string $group): self
    {
        return $this->add($group, LineType::Brand);
    }

    public function formatted(string $message): self
    {
        return $this->add($message, LineType::Formatted);
    }
}
