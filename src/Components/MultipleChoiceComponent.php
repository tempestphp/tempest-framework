<?php

declare(strict_types=1);

namespace Tempest\Console\Components;

use Tempest\Console\ConsoleComponent;
use Tempest\Console\HandlesKey;
use Tempest\Console\Key;

final class MultipleChoiceComponent implements ConsoleComponent
{
    public array $selectedOptions = [];
    public int $activeOption;

    public function __construct(
        public string $question,
        public array $options,
    ) {
        $this->activeOption = array_key_first($this->options);
    }

    public function render(): string
    {
        $output = "<question> {$this->question} </question>";

        foreach ($this->options as $key => $option) {
            $output .= PHP_EOL;

            $output .= $this->isActive($key) ? '> ' : '  ';
            $output .= $this->isSelected($key) ? '[x]' : '[ ]';
            $output .= $this->isActive($key) ? '<em>' : '';
            $output .= " $option";
            $output .= $this->isActive($key) ? '</em>' : '';
        }

        return $output . PHP_EOL . PHP_EOL . "Press <em>space</em> to select, press <em>enter</em> to confirm" . PHP_EOL;
    }

    public function isActive(int $key): bool
    {
        return $this->activeOption === $key;
    }

    public function isSelected(int $key): bool
    {
        return $this->selectedOptions[$key] ?? false;
    }

    #[HandlesKey(Key::SPACE)]
    public function toggleSelected(): void
    {
        $this->selectedOptions[$this->activeOption] = ! $this->isSelected($this->activeOption);
    }

    #[HandlesKey(Key::ENTER)]
    public function enter(): array
    {
        $result = [];

        foreach ($this->options as $key => $option)
        {
            if ($this->isSelected($key)) {
                $result[] = $option;
            }
        }

        return $result;
    }

    #[HandlesKey(Key::UP)]
    #[HandlesKey(Key::LEFT)]
    public function up(): void
    {
        $this->activeOption = $this->activeOption - 1;

        if ($this->activeOption < 0) {
            $this->activeOption = count($this->options) - 1;
        }
    }

    #[HandlesKey(Key::DOWN)]
    #[HandlesKey(Key::RIGHT)]
    public function down(): void
    {
        $this->activeOption = $this->activeOption + 1;

        if ($this->activeOption > count($this->options) - 1) {
            $this->activeOption = 0;
        }
    }
}
