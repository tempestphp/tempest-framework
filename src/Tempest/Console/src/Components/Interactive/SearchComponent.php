<?php

declare(strict_types=1);

namespace Tempest\Console\Components\Interactive;

use Closure;
use Tempest\Console\CanCancel;
use Tempest\Console\Components\Concerns\HasErrors;
use Tempest\Console\Components\Concerns\HasState;
use Tempest\Console\Components\Concerns\HasTextBuffer;
use Tempest\Console\Components\Concerns\RendersControls;
use Tempest\Console\Components\OptionCollection;
use Tempest\Console\Components\Renderers\ChoiceRenderer;
use Tempest\Console\Components\State;
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

final class SearchComponent implements InteractiveConsoleComponent, HasCursor, HasStaticComponent, CanCancel
{
    use HasErrors;
    use HasState;
    use RendersControls;
    use HasTextBuffer;

    private ChoiceRenderer $renderer;
    private OptionCollection $options;
    private ?string $previousQuery = null;

    public function __construct(
        public string $label,
        public Closure $search,
        public ?string $default = null,
    ) {
        $this->buffer = new TextBuffer();
        $this->renderer = new ChoiceRenderer(default: $default, multiple: false);
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
        );
    }

    private function updateQuery(): void
    {
        if ($this->previousQuery === $this->buffer->text) {
            return;
        }

        $this->options = new OptionCollection(array_values(($this->search)($this->buffer->text)));
        $this->previousQuery = $this->buffer->text;
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

    public function getCursorPosition(Terminal $terminal): Point
    {
        return $this->renderer->getCursorPosition($terminal, $this->buffer);
    }

    public function cursorVisible(): bool
    {
        return true;
    }

    public function getStaticComponent(): StaticConsoleComponent
    {
        return new StaticSearchComponent(
            label: $this->label,
            search: $this->search,
            default: $this->default,
        );
    }

    #[HandlesKey]
    public function input(string $key): void
    {
        $this->buffer->input($key);
    }

    #[HandlesKey(Key::ENTER)]
    public function enter(): null|int|string
    {
        return $this->options->getActive()->value;
    }

    #[HandlesKey(Key::UP)]
    public function up(): void
    {
        $this->options->previous();
    }

    #[HandlesKey(Key::DOWN)]
    public function down(): void
    {
        $this->options->next();
    }

    #[HandlesKey(Key::CTRL_C)]
    public function cancel(): ?string
    {
        $this->state = State::CANCELLED;

        return $this->default;
    }
}
