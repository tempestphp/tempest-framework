<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\Console\Styling\ConsoleOutputBuilder;

trait HandlesConsoleInput
{
    public function __construct(
        private readonly ConsoleOutput $output,
    ) {
    }

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
    ): string {
        $message = ConsoleOutputBuilder::new(" ")
            ->error(" $question ")
            ->when(! ! $options, function (ConsoleOutputBuilder $builder) use ($options, $default) {
                $builder->formatted("[" . ConsoleStyle::FG_BLUE(implode(', ', $options)) . "]")
                    ->formatted($default ? ConsoleStyle::FG_GRAY(" (default: $default) ") : " ");
            });

        $this->output->write(
            $message->toString(),
        );

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
