<?php

declare(strict_types=1);

namespace Tempest\Console;

use BackedEnum;
use Closure;
use Stringable;
use Symfony\Component\Process\Process;
use Tempest\Highlight\Language;
use UnitEnum;

interface Console
{
    public function call(string|array $command, string|array $arguments = []): ExitCode|int;

    /**
     * Reads a line from the console input.
     */
    public function readln(): string;

    /**
     * Reads the specified number of bytes from the console input.
     */
    public function read(int $bytes): string;

    /**
     * Writes the specified `$contents` to the console output.
     */
    public function write(string $contents): self;

    /**
     * Writes the specified `$contents` to the console output and appends a new line.
     */
    public function writeln(string $line = ''): self;

    /**
     * Writes the specified `$contents` to the console output, without formatting.
     */
    public function writeRaw(string $contents): self;

    /**
     * Writes the specified `$contents` to the console output with the specified syntax highlighting.
     */
    public function writeWithLanguage(string $contents, Language $language): self;

    /**
     * @param \Tempest\Validation\Rule[] $validation
     */
    public function component(InteractiveConsoleComponent $component, array $validation = []): mixed;

    /**
     * Asks the user a question and returns the answer.
     *
     * @param null|array|iterable|class-string<BackedEnum> $options
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
    ): null|int|string|Stringable|UnitEnum|array;

    /**
     * Asks the user a question and returns the answer.
     */
    public function confirm(string $question, bool $default = false, ?string $yes = null, ?string $no = null): bool;

    /**
     * Prompts the user for a password and returns it.
     */
    public function password(string $label = 'Password', bool $confirm = false, array $validation = []): ?string;

    /**
     * Progresses through the specified `$data` using the specified `$handler`.
     */
    public function progressBar(iterable $data, Closure $handler): array;

    /**
     * Asks the user to select an option from a list using a closure.
     *
     * @param Closure(string $search): array $search
     */
    public function search(string $label, Closure $search, bool $multiple = false, null|string|array $default = null): mixed;

    /**
     * Displays the progress of a task.
     */
    public function task(string $label, null|Process|Closure $handler): bool;

    /**
     * Displays a header.
     */
    public function header(string $header, ?string $subheader = null): self;

    /**
     * Displays information to the user.
     */
    public function info(string $contents, ?string $title = null): self;

    /**
     * Displays an error message to the user.
     */
    public function error(string $contents, ?string $title = null): self;

    /**
     * Displays a warning to the user.
     */
    public function warning(string $contents, ?string $title = null): self;

    /**
     * Displays a success message to the user.
     */
    public function success(string $contents, ?string $title = null): self;

    /**
     * Displays a key/value pair in a line.
     */
    public function keyValue(string $key, ?string $value = null, bool $useAvailableWidth = false): self;

    /**
     * Displays instructions to the user. Can be an array of lines.
     */
    public function instructions(array|string $lines): self;

    /**
     * Applies the specified `$callback` when the `$condition` is `true`.
     *
     * @param mixed|Closure(self): bool $condition
     * @param Closure(self): self $callback
     */
    public function when(mixed $condition, Closure $callback): self;

    /**
     * Applies the specified `$callback` unless the `$condition` is `true`.
     *
     * @param mixed|Closure(self): bool $condition
     * @param Closure(self): self $callback
     */
    public function unless(mixed $condition, Closure $callback): self;

    public function withLabel(string $label): self;

    /**
     * Whether the console is interactive.
     */
    public function supportsPrompting(): bool;

    /**
     * Forces the console to not be interactive.
     */
    public function disablePrompting(): self;
}
