<?php

namespace Tempest\Console\Components\Static;

use Tempest\Console\Components\StaticComponent;
use Tempest\Console\Console;

final readonly class StaticOptionComponent implements StaticComponent
{
    public function __construct(
        public string $question,
        public array $options,
        public ?string $default = null,
    ) {}

    public function render(Console $console): string
    {
        $answer = null;

        while (! $this->isValid($answer)) {
            $answer = $this->askQuestion($console);
        }

        return $answer;
    }

    private function askQuestion(Console $console): string
    {
        $console->write("<h2>{$this->question}</h2> ");

        $parsedOptions = [];

        foreach ($this->options as $option) {
            if ($option === $this->default) {
                $option = "<em><u>{$option}</u></em>";
            }

            $parsedOptions[] = $option;
        }

        $console->write('[' . implode('/', $parsedOptions) . '] ');

        $answer = trim($console->readln());

        return $answer === ''
            ? ($this->default ?? '')
            : $answer;
    }

    private function isValid(?string $answer): bool
    {
        if ($answer === null) {
            return false;
        }

        return in_array(strtolower($answer), $this->options, true);
    }
}