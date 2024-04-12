<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\Console\Actions\RenderConsoleComponent;
use Tempest\Console\Components\QuestionComponent;
use Tempest\Console\Components\TextBoxComponent;

final class GenericConsole implements Console
{
    public function __construct(
        private readonly ConsoleInput $input,
        private readonly ConsoleOutput $output,
    ) {
    }

    public function delimiter(string $delimiter): ConsoleOutput
    {
        return $this->output->delimiter($delimiter);
    }

    public function readln(): string
    {
        return $this->input->readln();
    }

    public function read(int $bytes): string
    {
        return $this->input->read($bytes);
    }

    public function ask(string $question, ?array $options = null): string
    {
        if ($options === null) {
            return $this->component(new TextBoxComponent($question));
        }

        return $this->component(new QuestionComponent($question, $options));
    }

    public function confirm(string $question, bool $default = false): bool
    {
        $result = $this->component(new QuestionComponent($question, ['yes', 'no']));

        return match($result) {
            'yes' => true,
            default => false,
        };
    }

    public function write(string $line, ConsoleOutputType $type = ConsoleOutputType::DEFAULT): self
    {
        $this->output->write($line, $type);

        return $this;
    }

    public function writeln(string $line = '', ConsoleOutputType $type = ConsoleOutputType::DEFAULT): self
    {
        $this->output->writeln($line, $type);

        return $this;
    }

    public function info(string $line): ConsoleOutput
    {
        return $this->output->info($line);
    }

    public function error(string $line): ConsoleOutput
    {
        return $this->output->error($line);
    }

    public function success(string $line): ConsoleOutput
    {
        return $this->output->success($line);
    }

    public function when(mixed $expression, callable $callback): ConsoleOutput
    {
        return $this->output->when($expression, $callback);
    }

    public function component(ConsoleComponent $component): mixed
    {
        return (new RenderConsoleComponent($this))($component);
    }
}
