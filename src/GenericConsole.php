<?php

declare(strict_types=1);

namespace Tempest\Console;

use Closure;
use Tempest\Console\Components\ComponentRenderer;
use Tempest\Console\Components\ConfirmComponent;
use Tempest\Console\Components\MultipleChoiceComponent;
use Tempest\Console\Components\PasswordComponent;
use Tempest\Console\Components\ProgressBarComponent;
use Tempest\Console\Components\QuestionComponent;
use Tempest\Console\Components\SearchComponent;
use Tempest\Console\Components\TextBoxComponent;
use Tempest\Console\Highlight\TempestConsoleLanguage;
use Tempest\Console\Input\InputBuffer;
use Tempest\Console\Output\OutputBuffer;
use Tempest\Highlight\Highlighter;

final class GenericConsole implements Console
{
    private ?string $label = null;
    private bool $isForced = false;

    public function __construct(
        private readonly OutputBuffer $output,
        private readonly InputBuffer $input,
        private readonly ComponentRenderer $componentRenderer,
        private readonly Highlighter $highlighter,
    ) {
    }

    public function setForced(): self
    {
        $this->isForced = true;

        return $this;
    }

    public function read(int $bytes): string
    {
        return $this->input->read($bytes);
    }

    public function readln(): string
    {
        return $this->input->readln();
    }

    public function write(string $contents): static
    {
        if ($this->label) {
            $contents = "<h2>{$this->label}</h2> {$contents}";
        }

        $this->output->write($this->highlighter->parse($contents, new TempestConsoleLanguage()));

        return $this;
    }

    public function writeln(string $line = ''): static
    {
        $this->write($line . PHP_EOL);

        return $this;
    }

    public function info(string $line): self
    {
        $this->writeln("<em>{$line}</em>");

        return $this;
    }

    public function error(string $line): self
    {
        $this->writeln("<error>{$line}</error>");

        return $this;
    }

    public function success(string $line): self
    {
        $this->writeln("<success>{$line}</success>");

        return $this;
    }

    public function withLabel(string $label): self
    {
        $clone = clone $this;

        $clone->label = $label;

        return $clone;
    }

    public function when(mixed $expression, callable $callback): self
    {
        if ($expression) {
            $callback($this);
        }

        return $this;
    }

    public function component(ConsoleComponent $component, array $validation = []): mixed
    {
        return $this->componentRenderer->render($this, $component, $validation);
    }

    public function ask(string $question, ?array $options = null, bool $multiple = false, array $validation = []): string|array
    {
        if ($options === null || $options === []) {
            $component = new TextBoxComponent($question);
        } elseif ($multiple) {
            $component = new MultipleChoiceComponent($question, $options);
        } else {
            $component = new QuestionComponent($question, $options);
        }

        return $this->component($component, $validation);
    }

    public function confirm(string $question, bool $default = false): bool
    {
        if ($this->isForced) {
            return true;
        }

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

    public function search(string $label, Closure $search): mixed
    {
        return $this->component(new SearchComponent($label, $search));
    }
}
