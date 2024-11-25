<?php

declare(strict_types=1);

namespace Tempest\Console\Components\Concerns;

use Tempest\Console\Components\Renderers\TextInputRenderer;
use Tempest\Console\Components\State;
use Tempest\Console\HandlesKey;
use Tempest\Console\Key;

trait HasTextInputRenderer
{
    use HasTextBuffer;
    use HasState;

    public TextInputRenderer $renderer;

    public bool $multiline = false;

    #[HandlesKey(Key::ENTER)]
    public function enter(): ?string
    {
        if ($this->multiline) {
            $this->state = State::ACTIVE;
            $this->buffer->input(PHP_EOL);

            return null;
        }

        return $this->buffer->text ?? '';
    }

    #[HandlesKey(Key::ALT_ENTER)]
    public function altEnter(): ?string
    {
        if (! $this->multiline) {
            return null;
        }

        $this->state = State::SUBMITTED;

        return $this->buffer->text ?? '';
    }

    #[HandlesKey(Key::UP)]
    public function up(): void
    {
        if (! $this->multiline) {
            $this->buffer->moveCursorToStartOfLine();

            return;
        }

        $this->buffer->moveCursorY(-1);
    }

    #[HandlesKey(Key::DOWN)]
    public function down(): void
    {
        if (! $this->multiline) {
            $this->buffer->moveCursorToEndOfLine();

            return;
        }

        $this->buffer->moveCursorY(1);
    }

    #[HandlesKey]
    public function input(string $key): void
    {
        if ($key === "\n" && ! $this->multiline) {
            return;
        }

        $this->buffer->input($key);
    }
}
