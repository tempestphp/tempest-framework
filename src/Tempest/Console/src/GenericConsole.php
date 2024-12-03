<?php

declare(strict_types=1);

namespace Tempest\Console;

use Closure;
use Tempest\Console\Actions\ExecuteConsoleCommand;
use Tempest\Console\Components\Interactive\ConfirmComponent;
use Tempest\Console\Components\Interactive\MultipleChoiceComponent;
use Tempest\Console\Components\Interactive\PasswordComponent;
use Tempest\Console\Components\Interactive\ProgressBarComponent;
use Tempest\Console\Components\Interactive\SearchComponent;
use Tempest\Console\Components\Interactive\SingleChoiceComponent;
use Tempest\Console\Components\Interactive\TextBoxComponent;
use Tempest\Console\Components\InteractiveComponentRenderer;
use Tempest\Console\Exceptions\UnsupportedComponent;
use Tempest\Console\Highlight\TempestConsoleLanguage\TempestConsoleLanguage;
use Tempest\Console\Input\ConsoleArgumentBag;
use Tempest\Container\Tag;
use Tempest\Highlight\Highlighter;
use Tempest\Highlight\Language;
use Tempest\Support\Conditions\HasConditions;

final class GenericConsole implements Console
{
    use HasConditions;

    private ?string $label = null;

    private bool $isForced = false;

    private bool $supportsTty = true;

    private bool $supportsPrompting = true;

    private ?InteractiveComponentRenderer $componentRenderer = null;

    public function __construct(
        private readonly OutputBuffer $output,
        private readonly InputBuffer $input,
        #[Tag('console')]
        private readonly Highlighter $highlighter,
        private readonly ExecuteConsoleCommand $executeConsoleCommand,
        private readonly ConsoleArgumentBag $argumentBag
    ) {
    }

    public function call(string $command): ExitCode|int
    {
        return ($this->executeConsoleCommand)($command);
    }

    public function setComponentRenderer(InteractiveComponentRenderer $componentRenderer): self
    {
        $this->componentRenderer = $componentRenderer;

        return $this;
    }

    public function setForced(): self
    {
        $this->isForced = true;

        return $this;
    }

    public function disableTty(): self
    {
        $this->supportsTty = false;

        return $this;
    }

    public function disablePrompting(): self
    {
        $this->supportsPrompting = false;

        return $this;
    }

    public function read(int $bytes): string
    {
        if (! $this->supportsPrompting()) {
            return '';
        }

        return $this->input->read($bytes);
    }

    public function readln(): string
    {
        if (! $this->supportsPrompting()) {
            return '';
        }

        return $this->input->readln();
    }

    public function write(string $contents): static
    {
        $this->writeWithLanguage($contents, new TempestConsoleLanguage());

        return $this;
    }

    public function writeln(string $line = ''): static
    {
        $this->write($line . PHP_EOL);

        return $this;
    }

    public function writeWithLanguage(string $contents, Language $language): Console
    {
        if ($this->label) {
            $contents = "<h2>{$this->label}</h2> {$contents}";
        }

        $this->output->write($this->highlighter->parse($contents, $language));

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

    public function component(InteractiveConsoleComponent $component, array $validation = []): mixed
    {
        if ($this->supportsTty()) {
            return $this->componentRenderer->render($this, $component, $validation);
        }

        if ($component instanceof HasStaticComponent) {
            return $component->getStaticComponent()->render($this);
        }

        throw new UnsupportedComponent($component);
    }

    public function ask(
        string $question,
        ?array $options = null,
        mixed $default = null,
        bool $multiple = false,
        bool $asList = false,
        array $validation = [],
    ): null|string|array {
        if ($this->isForced && $default) {
            return $default;
        }

        if ($options === null || $options === []) {
            $component = new TextBoxComponent($question, $default);
        } elseif ($multiple) {
            $component = new MultipleChoiceComponent(
                question: $question,
                options: $options,
                default: is_array($default) ? $default : [$default],
            );
        } else {
            $component = new SingleChoiceComponent(
                question: $question,
                options: $options,
                default: $default,
                asList: $asList,
            );
        }

        return $this->component($component, $validation);
    }

    public function confirm(string $question, bool $default = false): bool
    {
        if ($this->isForced) {
            return true;
        }

        return $this->component(new ConfirmComponent($question, $default));
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

    public function search(string $label, Closure $search, ?string $default = null): mixed
    {
        return $this->component(new SearchComponent($label, $search, $default));
    }

    public function supportsTty(): bool
    {
        if ($this->supportsTty === false) {
            return false;
        }

        if ($this->componentRenderer === null) {
            return false;
        }

        if (! $this->supportsPrompting()) {
            return false;
        }

        return (bool) shell_exec('which tput && which stty');
    }

    public function supportsPrompting(): bool
    {
        if ($this->supportsPrompting === false) {
            return false;
        }

        if ($this->argumentBag->get('interaction')?->value === false) {
            return false;
        }

        return true;
    }
}
