<?php

declare(strict_types=1);

namespace Tempest\Console\Components\Static;

use Tempest\Console\Console;
use Tempest\Console\StaticComponent;

final readonly class StaticSingleChoiceComponent implements StaticComponent
{
    public function __construct(
        public string $question,
        public array $options,
        public mixed $default = null,
        public bool $asList = false,
    ) {
    }

    public function render(Console $console): string
    {
        $console->write("<h2>{$this->question}</h2> ");

        $parsedOptions = [];

        if ($this->asList) {
            foreach ($this->options as $key => $option) {
                $key = $key === $this->default
                    ? "<em><strong>{$key}</strong></em>"
                    : $key;

                $parsedOptions[$key] = "- [{$key}] {$option}";
            }

            $console->write(PHP_EOL . implode(PHP_EOL, $parsedOptions) . PHP_EOL);
        } else {
            foreach ($this->options as $option) {
                if ($option === $this->default) {
                    $option = "<em><strong>{$option}</strong></em>";
                }

                $parsedOptions[] = $option;
            }

            $console->write('[' . implode('/', $parsedOptions) . '] ');
        }

        $answer = trim($console->readln());

        if ($answer === '' && $this->default) {
            return $this->options[$this->default] ?? $this->default;
        }

        if ($this->asList && array_key_exists($answer, $this->options)) {
            return $this->options[$answer];
        }

        if (! $this->asList && in_array($answer, $this->options)) {
            return $answer;
        }

        return $this->render($console);
    }
}
