<?php

declare(strict_types=1);

namespace Tempest\Console;

use Closure;
use Tempest\Highlight\Language;

interface Console
{
    public function call(string $command): ExitCode|int;

    public function readln(): string;

    public function read(int $bytes): string;

    public function write(string $contents): self;

    public function writeln(string $line = ''): self;

    public function writeWithLanguage(string $contents, Language $language): self;

    /**
     * @param \Tempest\Validation\Rule[] $validation
     */
    public function component(InteractiveConsoleComponent $component, array $validation = []): mixed;

    /**
     * @param mixed|null $default
     * @param \Tempest\Validation\Rule[] $validation
     */
    public function ask(
        string $question,
        ?array $options = null,
        mixed $default = null,
        bool $multiple = false,
        bool $asList = false,
        array $validation = [],
    ): null|string|array;

    public function confirm(string $question, bool $default = false): bool;

    public function password(string $label = 'Password', bool $confirm = false): string;

    public function progressBar(iterable $data, Closure $handler): array;

    /**
     * @param Closure(string $search): array $search
     */
    public function search(string $label, Closure $search, ?string $default = null): mixed;

    public function info(string $line): self;

    public function error(string $line): self;

    public function success(string $line): self;

    /**
     * @param mixed|Closure(self): bool $condition
     * @param Closure(self): self $callback
     */
    public function when(mixed $condition, Closure $callback): self;

    /**
     * @param mixed|Closure(self): bool $condition
     * @param Closure(self): self $callback
    */
    public function unless(mixed $condition, Closure $callback): self;

    public function withLabel(string $label): self;

    public function supportsTty(): bool;

    public function supportsPrompting(): bool;

    public function disableTty(): self;

    public function disablePrompting(): self;
}
