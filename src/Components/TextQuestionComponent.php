<?php

declare(strict_types=1);

namespace Tempest\Console\Components;

use Tempest\Console\ConsoleComponent;
use Tempest\Console\HandlesKey;
use Tempest\Console\Key;

final class TextQuestionComponent implements ConsoleComponent
{
    public string $answer = '';

    public function __construct(
        public string $question,
    ) {}

    public function render(): string
    {
        $output = "<question> {$this->question} </question>";

        $output .= PHP_EOL;
        $output .= '> ' . $this->answer;

        return  $output;
    }

    #[HandlesKey(Key::ENTER)]
    public function enter(): string
    {
        return $this->answer;
    }

    #[HandlesKey]
    public function input(string $key): void
    {
        $this->answer .= $key;
    }
}
