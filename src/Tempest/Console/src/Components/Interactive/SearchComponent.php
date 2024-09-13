<?php

declare(strict_types=1);

namespace Tempest\Console\Components\Interactive;

use Closure;
use Generator;
use Tempest\Console\Components\Static\StaticSearchComponent;
use Tempest\Console\HandlesKey;
use Tempest\Console\HasCursor;
use Tempest\Console\HasStaticComponent;
use Tempest\Console\InteractiveComponent;
use Tempest\Console\Key;
use Tempest\Console\Point;
use Tempest\Console\StaticComponent;

final class SearchComponent implements InteractiveComponent, HasCursor, HasStaticComponent
{
    public Point $cursorPosition;

    public string $query = '';

    public int $selectedOption = 0;

    public array $options = [];

    public function __construct(
        public string $label,
        public Closure $search,
    ) {
        $this->cursorPosition = new Point(2, 1);
    }

    public function render(): Generator|string
    {
        $output = "<question>{$this->label}</question> {$this->query}";

        foreach ($this->options as $key => $option) {
            $output .= PHP_EOL;
            $output .= $this->isSelected($key) ? "[x] <em>{$option}</em>" : "[ ] {$option}";
        }

        return $output;
    }

    public function renderFooter(): string
    {
        return "Press <em>up</em>/<em>down</em> to select, <em>enter</em> to confirm, <em>ctrl+c</em> to cancel";
    }

    #[HandlesKey]
    public function input(string $key): void
    {
        if (str_starts_with($key, "\e")) {
            return;
        }

        $offset = $this->cursorPosition->x - 2;

        $this->updateQuery(substr($this->query, 0, $offset) . $key . substr($this->query, $offset));

        $this->right();
    }

    #[HandlesKey(Key::ENTER)]
    public function enter(): ?string
    {
        $selected = $this->options[$this->selectedOption] ?? null;

        if (! $selected) {
            return null;
        }

        $this->query = $selected;

        return $selected;
    }

    #[HandlesKey(Key::BACKSPACE)]
    public function backspace(): void
    {
        $offset = $this->cursorPosition->x - 2;

        if ($offset <= 0) {
            return;
        }

        $this->updateQuery(substr($this->query, 0, $offset - 1) . substr($this->query, $offset));

        $this->left();
    }

    #[HandlesKey(Key::DELETE)]
    public function delete(): void
    {
        $offset = $this->cursorPosition->x - 2;

        $this->updateQuery(substr($this->query, 0, $offset) . substr($this->query, $offset + 1));
    }

    #[HandlesKey(Key::HOME)]
    public function home(): void
    {
        $this->cursorPosition->x = 2;
    }

    #[HandlesKey(Key::END)]
    public function end(): void
    {
        $this->cursorPosition->x = strlen($this->query) + 2;
    }

    #[HandlesKey(Key::LEFT)]
    public function left(): void
    {
        $this->cursorPosition->x = max(2, $this->cursorPosition->x - 1);
    }

    #[HandlesKey(Key::RIGHT)]
    public function right(): void
    {
        $this->cursorPosition->x = min(strlen($this->query) + 2, $this->cursorPosition->x + 1);
    }

    #[HandlesKey(Key::UP)]
    public function up(): void
    {
        $this->selectedOption = $this->selectedOption - 1;

        if ($this->selectedOption < 0) {
            $this->selectedOption = count($this->options) - 1;
        }
    }

    #[HandlesKey(Key::DOWN)]
    public function down(): void
    {
        $this->selectedOption = $this->selectedOption + 1;

        if ($this->selectedOption > count($this->options) - 1) {
            $this->selectedOption = 0;
        }
    }

    public function getCursorPosition(): Point
    {
        return new Point(
            x: $this->cursorPosition->x + strlen($this->label) + 1,
            y: $this->cursorPosition->y - 1,
        );
    }

    private function updateQuery(string $query): void
    {
        $this->selectedOption = 0;
        $this->query = $query;
        $this->options = array_values(($this->search)($this->query));
    }

    private function isSelected(int $key): bool
    {
        return $this->selectedOption === $key;
    }

    public function getStaticComponent(): StaticComponent
    {
        return new StaticSearchComponent(
            label: $this->label,
            search: $this->search,
        );
    }
}
