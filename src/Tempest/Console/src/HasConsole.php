<?php

declare(strict_types=1);

namespace Tempest\Console;

use Closure;
use Stringable;
use Symfony\Component\Process\Process;
use Tempest\Container\Inject;
use UnitEnum;

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
        null|iterable|string $options = null,
        mixed $default = null,
        bool $multiple = false,
        bool $multiline = false,
        ?string $placeholder = null,
        ?string $hint = null,
        array $validation = [],
    ): null|int|string|Stringable|UnitEnum|array {
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

    public function confirm(
        string $question,
        bool $default = false,
        ?string $yes = null,
        ?string $no = null,
    ): bool {
        return $this->console->confirm(
            question: $question,
            default: $default,
            yes: $yes,
            no: $no,
        );
    }

    public function password(string $label = 'Password', bool $confirm = false, array $validation = []): string
    {
        return $this->console->password(
            label: $label,
            confirm: $confirm,
            validation: $validation,
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

    public function info(string $contents, ?string $title = null): self
    {
        $this->console->info($contents, $title);

        return $this;
    }

    public function error(string $contents, ?string $title = null): self
    {
        $this->console->error($contents, $title);

        return $this;
    }

    public function warning(string $contents, ?string $title = null): self
    {
        $this->console->warning($contents, $title);

        return $this;
    }

    public function success(string $contents, ?string $title = null): self
    {
        $this->console->success($contents, $title);

        return $this;
    }

    public function keyValue(string $key, ?string $value = null): self
    {
        $this->console->keyValue($key, $value);

        return $this;
    }

    public function task(string $label, null|Process|Closure $handler): bool
    {
        return $this->console->task($label, $handler);
    }
}
