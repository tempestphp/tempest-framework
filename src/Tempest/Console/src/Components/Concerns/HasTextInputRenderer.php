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
    public function newLine(): ?string
    {
        if ($this->multiline) {
            $this->state = State::SUBMITTED;

            return $this->buffer->text ?? '';
        }

        $this->buffer->input(PHP_EOL);
    }

    #[HandlesKey(Key::CTRL_LEFT)]
    public function moveCursorToPreviousWord(): void
    {
        $this->buffer->moveCursorToPreviousWord();
    }

    #[HandlesKey(Key::HOME)]
    public function start(): void
    {
        $this->buffer->moveCursorToStart();
    }

    #[HandlesKey(Key::END_OF_LINE)]
    public function endOfLine(): void
    {
        $this->buffer->moveCursorToEndOfLine();
    }

    #[HandlesKey(Key::CTRL_RIGHT)]
    public function moveCursorToNextWord(): void
    {
        $this->buffer->moveCursorToNextWord();
    }

    #[HandlesKey(Key::END)]
    public function end(): void
    {
        $this->buffer->moveCursorToEnd();
    }

    #[HandlesKey(Key::START_OF_LINE)]
    public function startOfLine(): void
    {
        $this->buffer->moveCursorToStartOfLine();
    }

    #[HandlesKey(Key::LEFT)]
    public function left(): void
    {
        $this->buffer->moveCursorX(-1);
    }

    #[HandlesKey(Key::RIGHT)]
    public function right(): void
    {
        $this->buffer->moveCursorX(1);
    }

    #[HandlesKey(Key::DELETE)]
    public function deleteNextCharacter(): void
    {
        $this->buffer->deleteNextCharacter();
    }

    #[HandlesKey(Key::CTRL_DELETE)]
    public function deleteNextWord(): void
    {
        $this->buffer->deleteNextWord();
    }

    #[HandlesKey(Key::BACKSPACE)]
    public function deletePreviousCharacter(): void
    {
        $this->buffer->deletePreviousCharacter();
    }

    #[HandlesKey(Key::CTRL_BACKSPACE)]
    public function deletePreviousWord(): void
    {
        $this->buffer->deletePreviousWord();
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
        $this->buffer->input($key);
    }
}
