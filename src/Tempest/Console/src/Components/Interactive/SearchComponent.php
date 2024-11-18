<?php

declare(strict_types=1);

namespace Tempest\Console\Components\Interactive;

use Closure;
use Tempest\Console\Components\Concerns\HasErrors;
use Tempest\Console\Components\Concerns\HasState;
use Tempest\Console\Components\Concerns\HasTextBuffer;
use Tempest\Console\Components\Concerns\RendersControls;
use Tempest\Console\Components\Renderers\SearchRenderer;
use Tempest\Console\Components\Static\StaticSearchComponent;
use Tempest\Console\Components\TextBuffer;
use Tempest\Console\HandlesKey;
use Tempest\Console\HasCursor;
use Tempest\Console\HasStaticComponent;
use Tempest\Console\InteractiveConsoleComponent;
use Tempest\Console\Key;
use Tempest\Console\Point;
use Tempest\Console\StaticConsoleComponent;
use Tempest\Console\Terminal\Terminal;

final class SearchComponent implements InteractiveConsoleComponent, HasCursor, HasStaticComponent
{
    use HasErrors;
    use HasState;
    use RendersControls;
    use HasTextBuffer;

    private SearchRenderer $renderer;

    private null|int|string $activeOption = null;
    private array $options = [];
    private ?string $previousQuery = null;

    public function __construct(
        public string $label,
        public Closure $search,
        public ?string $default = null,
    ) {
        $this->buffer = new TextBuffer();
        $this->renderer = new SearchRenderer();
        $this->updateQuery();
    }

    public function render(Terminal $terminal): string
    {
        $this->updateQuery();

        return $this->renderer->render(
            terminal: $terminal,
            state: $this->state,
            label: $this->label,
            query: $this->buffer,
            options: $this->options,
            selected: $this->activeOption,
        );
    }

    private function getControls(): array
    {
        return [
            '↑' => 'up',
            '↓' => 'down',
            'enter' => 'confirm',
            'ctrl+c' => 'cancel',
        ];
    }

    #[HandlesKey]
    public function input(string $key): void
    {
        $this->buffer->input($key);
    }

    private function updateQuery(): void
    {
        if ($this->previousQuery === $this->buffer->text) {
            return;
        }

        $this->options = array_values(($this->search)($this->buffer->text));
        $this->activeOption = array_key_first($this->options);
        $this->previousQuery = $this->buffer->text;
    }

    #[HandlesKey(Key::ENTER)]
    public function enter(): null|int|string
    {
        if (! $value = $this->options[$this->activeOption] ?? null) {
            return $this->default;
        }

        return array_is_list($this->options)
            ? $value
            : $this->activeOption;
    }

    #[HandlesKey(Key::UP)]
    public function up(): void
    {
        $previousValue = prev($this->options);

        if ($previousValue === false) {
            end($this->options);
        }

        $this->activeOption = key($this->options);
    }

    #[HandlesKey(Key::DOWN)]
    public function down(): void
    {
        $nextValue = next($this->options);

        if ($nextValue === false) {
            reset($this->options);
        }

        $this->activeOption = key($this->options);
    }

    public function getCursorPosition(Terminal $terminal): Point
    {
        return $this->renderer->getCursorPosition($terminal, $this->buffer);
    }

    public function getStaticComponent(): StaticConsoleComponent
    {
        return new StaticSearchComponent(
            label: $this->label,
            search: $this->search,
            default: $this->default,
        );
    }
}
