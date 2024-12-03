<?php

declare(strict_types=1);

namespace Tempest\Console;

use Closure;
use Tempest\Container\Inject;

trait HasConsole
{
    #[Inject]
    private readonly Console $console;

    public function readln(): string
    {
        return $this->console->readln();
    }

    public function read(int $bytes): string
    {
        return $this->console->read($bytes);
    }

    public function write(string $contents): self
    {
        $this->console->write($contents);

        return $this;
    }

    public function writeln(string $line = ''): self
    {
        $this->console->writeln($line);

        return $this;
    }

    /**
     * @param mixed|null $default
     * @param \Tempest\Validation\Rule[] $validation
     */
    public function ask(
        string $question,
        ?array $options = null,
        mixed $default = null,
        bool $multiple = false,
        bool $multiline = false,
        ?string $placeholder = null,
        ?string $hint = null,
        array $validation = [],
    ): string|array {
        return $this->console->ask(
            question: $question,
            options: $options,
            default: $default,
            multiple: $multiple,
            multiline: $multiline,
            placeholder: $placeholder,
            hint: $hint,
            validation: $validation,
        );
    }

    public function confirm(string $question, bool $default = false): bool
    {
        return $this->console->confirm(
            question: $question,
            default: $default,
        );
    }

    public function password(string $label = 'Password', bool $confirm = false): string
    {
        return $this->console->password(
            label: $label,
            confirm: $confirm,
        );
    }

    public function progressBar(iterable $data, Closure $handler): array
    {
        return $this->console->progressBar(
            data: $data,
            handler: $handler,
        );
    }

    /**
     * @param Closure(string $search): array $search
     */
    public function search(string $label, Closure $search, bool $multiple = false, null|string|array $default = null): mixed
    {
        return $this->console->search(
            label: $label,
            search: $search,
            multiple: $multiple,
            default: $default,
        );
    }

    public function info(string $line, ?string $symbol = null): self
    {
        $this->console->info($line, $symbol);

        return $this;
    }

    public function error(string $line, ?string $symbol = null): self
    {
        $this->console->error($line, $symbol);

        return $this;
    }

    public function warning(string $line, ?string $symbol = null): self
    {
        $this->console->warning($line, $symbol);

        return $this;
    }

    public function success(string $line, ?string $symbol = null): self
    {
        $this->console->success($line, $symbol);

        return $this;
    }
}
