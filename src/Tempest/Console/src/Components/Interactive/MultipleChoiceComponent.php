<?php

declare(strict_types=1);

namespace Tempest\Console\Components\Interactive;

use Tempest\Console\Components\Static\StaticMultipleChoiceComponent;
use Tempest\Console\HandlesKey;
use Tempest\Console\HasStaticComponent;
use Tempest\Console\InteractiveConsoleComponent;
use Tempest\Console\Key;
use Tempest\Console\StaticConsoleComponent;

final class MultipleChoiceComponent implements InteractiveConsoleComponent, HasStaticComponent
{
    public array $selectedOptions = [];

    public int|string $activeOption;

    public function __construct(
        public string $question,
        public array $options,
        public array $default = [],
    ) {
        foreach ($this->default as $key => $value) {
            $this->selectedOptions[array_is_list($options) ? $key : $value] = true;
        }

        $this->activeOption = array_key_first($this->options);
    }

    public function render(): string
    {
        $output = "<question>{$this->question}</question>";

        foreach ($this->options as $key => $option) {
            $output .= PHP_EOL;

            $output .= $this->isActive($key) ? '> ' : '  ';
            $output .= $this->isSelected($key) ? '[x]' : '[ ]';
            $output .= $this->isActive($key) ? '<em>' : '';
            $output .= " {$option}";
            $output .= $this->isActive($key) ? '</em>' : '';
        }

        return $output;
    }

    public function renderFooter(): string
    {
        return "Press <em>space</em> to select, <em>enter</em> to confirm, <em>ctrl+c</em> to cancel";
    }

    public function isActive(int|string $key): bool
    {
        return $this->activeOption === $key;
    }

    public function isSelected(int|string $key): bool
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

        foreach ($this->options as $key => $option) {
            if ($this->isSelected($key)) {
                $result[$key] = $option;
            }
        }

        return $result;
    }

    #[HandlesKey(Key::UP)]
    #[HandlesKey(Key::LEFT)]
    public function up(): void
    {
        $previousValue = prev($this->options);

        if ($previousValue === false) {
            end($this->options);
        }

        $this->activeOption = key($this->options);
    }

    #[HandlesKey(Key::DOWN)]
    #[HandlesKey(Key::RIGHT)]
    public function down(): void
    {
        $nextValue = next($this->options);

        if ($nextValue === false) {
            reset($this->options);
        }

        $this->activeOption = key($this->options);
    }

    public function getStaticComponent(): StaticConsoleComponent
    {
        return new StaticMultipleChoiceComponent(
            question: $this->question,
            options: $this->options,
            default: $this->default,
        );
    }
}
