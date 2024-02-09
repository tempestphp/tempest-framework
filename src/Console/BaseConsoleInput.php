<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\Interface\ConsoleOutput;

trait BaseConsoleInput
{
    public function __construct(
        private readonly ConsoleOutput $output,
    ) {}

    public function readln(): string
    {
        $stream = fopen('php://stdin', 'r');

        $line = fgets($stream);

        fclose($stream);

        return $line;
    }

    public function ask(
        string $question,
        ?array $options = null,
        ?string $default = null,
    ): string
    {
        $questionString = $question;

        if ($options) {
            $questionString .= ' [' .
                implode(
                    ',',
                    array_map(
                        fn (string $option) => $option === $default ? strtoupper($option) : $option,
                        $options,
                    ),
                )
                . ']';
        }

        $this->output->info($questionString);

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
    ): bool
    {
        $answer = $this->ask($question, ['y','n'], $default ? 'y' : 'n');

        return strtolower($answer) === 'y';
    }
}
