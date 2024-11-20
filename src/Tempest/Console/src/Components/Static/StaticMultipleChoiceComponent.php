<?php

declare(strict_types=1);

namespace Tempest\Console\Components\Static;

use Tempest\Console\Console;
use Tempest\Console\StaticConsoleComponent;

final readonly class StaticMultipleChoiceComponent implements StaticConsoleComponent
{
    public function __construct(
        public string $question,
        public array $options,
        public array $default = [],
    ) {
    }

    public function render(Console $console): array
    {
        if (! $console->supportsPrompting()) {
            return array_is_list($this->options)
                ? array_filter($this->default, fn (mixed $value) => in_array($value, $this->options))
                : array_filter($this->default, fn (mixed $value) => array_key_exists($value, $this->options));
        }

        do {
            $answer = $this->askQuestion($console);

            $answerAsString = implode(', ', $answer) ?: 'no option';

            $confirm = $console->confirm(
                question: "You picked {$answerAsString}; continue?",
                default: true,
            );
        } while ($confirm === false);

        return $answer;
    }

    private function askQuestion(Console $console): array
    {
        $console->write("<h2>{$this->question}</h2> ");

        $parsedOptions = [];

        foreach ($this->options as $key => $option) {
            $parsedOptions[$key] = "- [{$key}] {$option}";
        }

        $console->write(PHP_EOL . implode(PHP_EOL, $parsedOptions) . PHP_EOL);

        $answers = explode(',', $console->readln());

        $validAnswers = [];

        foreach ($answers as $answer) {
            $answer = trim($answer);

            if (! array_key_exists($answer, $this->options)) {
                continue;
            }

            $validAnswers[] = $this->options[$answer];
        }

        return $validAnswers;
    }
}
