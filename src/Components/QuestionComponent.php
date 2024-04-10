<?php

declare(strict_types=1);

namespace Tempest\Console\Components;

use Tempest\Console\Component;
use Tempest\Console\HandlesKey;
use Tempest\Console\Highlight\IsComponent;
use Tempest\Console\Key;

final class QuestionComponent implements Component
{
    use IsComponent;

    public int $selectedOption;

    public function __construct(
        public string $question,
        public array $options,
    ) {
        $this->selectedOption = array_key_first($this->options);
    }

    private function getPath(): string
    {
        return __DIR__ . '/question.php';
    }

    public function isSelected(int $key): bool
    {
        return $this->selectedOption === $key;
    }

    #[HandlesKey(Key::ENTER)]
    public function enter(): string
    {
        return $this->options[$this->selectedOption] ?? '';
    }

    #[HandlesKey(Key::UP)]
    #[HandlesKey(Key::LEFT)]
    public function up(): void
    {
        $this->selectedOption = $this->selectedOption - 1;

        if ($this->selectedOption < 0) {
            $this->selectedOption = count($this->options) - 1;
        }
    }

    #[HandlesKey(Key::DOWN)]
    #[HandlesKey(Key::RIGHT)]
    public function down(): void
    {
        $this->selectedOption = $this->selectedOption + 1;

        if ($this->selectedOption > count($this->options) - 1) {
            $this->selectedOption = 0;
        }
    }
}
