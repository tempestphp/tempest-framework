<?php

declare(strict_types=1);

namespace Tempest\Console;

final readonly class GenericConsoleInput implements ConsoleInput
{
    public function __construct(
        private ConsoleOutput $output,
    ) {
    }

    public function readln(): string
    {
        $stream = fopen('php://stdin', 'r');

        $line = fgets($stream);

        fclose($stream);

        return $line;
    }

    public function read(int $bytes): string
    {
        return fread(STDIN, $bytes);
    }

    public function ask(
        string $question,
        ?array $options = null,
        ?string $default = null,
    ): string {
        $questionString = "<question>{$question}</question>";

        if ($options) {
            $questionString .= ' [<em>' .
                implode(
                    ',',
                    array_map(
                        fn (string $option) => $option === $default ? strtoupper($option) : $option,
                        $options,
                    ),
                )
                . '</em>] ';
        }

        $this->output->write($questionString);

        $answer = trim($this->readln());

        if ($answer === '' && $default) {
            return $default;
        }

        if (
            $options !== null
            && ! in_array(
                strtolower($answer),
                array_map(
                    fn (string $option) => strtolower($option),
                    $options,
                ),
            )
        ) {
            return $this->ask($question, $options, $default);
        }

        return $answer;
    }

    public function confirm(
        string $question,
        bool $default = false,
    ): bool {
        $answer = $this->ask($question, ['y', 'n'], $default ? 'y' : 'n');

        return strtolower($answer) === 'y';
    }
}
