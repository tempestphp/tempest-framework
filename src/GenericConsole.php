<?php

declare(strict_types=1);

namespace Tempest\Console;

use Closure;
use Tempest\Console\Actions\RenderConsoleComponent;
use Tempest\Console\Components\ConfirmComponent;
use Tempest\Console\Components\PasswordComponent;
use Tempest\Console\Components\ProgressBarComponent;
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

    public function write(string $contents): self
    {
        $this->output->write($contents);

        return $this;
    }

    public function writeln(string $line = ''): self
    {
        $this->output->writeln($line);

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

    public function component(ConsoleComponent $component, array $validation = []): mixed
    {
        return (new RenderConsoleComponent($this))($component, $validation);
    }

    public function ask(string $question, ?array $options = null, array $validation = []): string
    {
        $component = ($options === null || $options === [])
            ? new TextBoxComponent($question)
            : new QuestionComponent($question, $options);

        return $this->component($component, $validation);
    }

    public function confirm(string $question, bool $default = false): bool
    {
        return $this->component(new ConfirmComponent($question));
    }

    public function password(string $label = 'Password', bool $confirm = false): string
    {
        if (! $confirm) {
            return $this->component(new PasswordComponent($label));
        }

        $password = null;
        $passwordConfirm = null;

        while ($password === null || $password !== $passwordConfirm) {
            if ($password !== null) {
                $this->error("Passwords don't match");
            }

            $password = $this->component(new PasswordComponent($label));
            $passwordConfirm = $this->component(new PasswordComponent('Please confirm'));
        }

        return $password;
    }

    public function progressBar(iterable $data, Closure $handler): array
    {
        return $this->component(new ProgressBarComponent($data, $handler));
    }
}
