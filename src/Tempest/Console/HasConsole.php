<?php

declare(strict_types=1);

namespace Tempest\Console;

use Closure;

trait HasConsole
{
    public function __construct(private readonly Console $console)
    {
    }

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
     * @param string $question
     * @param array|null $options
     * @param mixed|null $default
     * @param bool $multiple
     * @param bool $asList
     * @param \Tempest\Validation\Rule[] $validation
     * @return string|array
     */
    public function ask(
        string $question,
        ?array $options = null,
        mixed $default = null,
        bool $multiple = false,
        bool $asList = false,
        array $validation = [],
    ): string|array {
        return $this->console->ask(
            question: $question,
            options: $options,
            default: $default,
            multiple: $multiple,
            asList: $asList,
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
     * @param string $label
     * @param Closure(string $search): array $search
     * @return mixed
     */
    public function search(string $label, Closure $search): mixed
    {
        return $this->console->search(
            label: $label,
            search: $search,
        );
    }

    public function info(string $line): self
    {
        $this->console->info($line);

        return $this;
    }

    public function error(string $line): self
    {
        $this->console->error($line);

        return $this;
    }

    public function success(string $line): self
    {
        $this->console->success($line);

        return $this;
    }
}
