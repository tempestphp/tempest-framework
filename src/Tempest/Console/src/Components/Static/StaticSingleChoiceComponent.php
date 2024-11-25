<?php

declare(strict_types=1);

namespace Tempest\Console\Components\Static;

use Tempest\Console\Console;
use Tempest\Console\StaticConsoleComponent;

final readonly class StaticSingleChoiceComponent implements StaticConsoleComponent
{
    public function __construct(
        public string $label,
        public array $options,
        public mixed $default = null,
    ) {
    }

    public function render(Console $console): ?string
    {
        if (! $console->supportsPrompting()) {
            return $this->default;
        }

        $console->write("<h2>{$this->label}</h2> ");

        $parsedOptions = [];

        foreach ($this->options as $key => $option) {
            $key = $key === $this->default
                ? "<em><strong>{$key}</strong></em>"
                : $key;

            $parsedOptions[$key] = "- [{$key}] {$option}";
        }

        $console->write(PHP_EOL . implode(PHP_EOL, $parsedOptions) . PHP_EOL);

        $answer = trim($console->readln());

        if (! $answer && $this->default) {
            return $this->options[$this->default] ?? $this->default;
        }

        if (array_is_list($this->options) && in_array($answer, $this->options)) {
            return $answer;
        }

        if (array_key_exists($answer, $this->options)) {
            return $this->options[$answer];
        }

        return $this->render($console);
    }
}
