<?php

declare(strict_types=1);

namespace Tempest\Console\Components\Concerns;

use Tempest\Console\Components\TextBuffer;
use Tempest\Console\HandlesKey;
use Tempest\Console\InteractiveConsoleComponent;
use Tempest\Console\Key;

/**
 * @mixin InteractiveConsoleComponent
 * @phpstan-require-implements InteractiveConsoleComponent
 */
trait HasTextBuffer
{
    public TextBuffer $buffer;

    public bool $bufferEnabled = true;

    public function setText(?string $text): void
    {
        if (! $this->bufferEnabled) {
            return;
        }

        $this->buffer->setText($text);
    }

    public function getText(): ?string
    {
        return $this->buffer->text;
    }

    #[HandlesKey(Key::CTRL_LEFT)]
    public function moveCursorToPreviousWord(): void
    {
        if (! $this->bufferEnabled) {
            return;
        }

        $this->buffer->moveCursorToPreviousWord();
    }

    #[HandlesKey(Key::HOME)]
    public function start(): void
    {
        if (! $this->bufferEnabled) {
            return;
        }

        $this->buffer->moveCursorToStart();
    }

    #[HandlesKey(Key::END_OF_LINE)]
    public function endOfLine(): void
    {
        if (! $this->bufferEnabled) {
            return;
        }

        $this->buffer->moveCursorToEndOfLine();
    }

    #[HandlesKey(Key::CTRL_RIGHT)]
    public function moveCursorToNextWord(): void
    {
        if (! $this->bufferEnabled) {
            return;
        }

        $this->buffer->moveCursorToNextWord();
    }

    #[HandlesKey(Key::END)]
    public function end(): void
    {
        if (! $this->bufferEnabled) {
            return;
        }

        $this->buffer->moveCursorToEnd();
    }

    #[HandlesKey(Key::START_OF_LINE)]
    public function startOfLine(): void
    {
        if (! $this->bufferEnabled) {
            return;
        }

        $this->buffer->moveCursorToStartOfLine();
    }

    #[HandlesKey(Key::LEFT)]
    public function left(): void
    {
        if (! $this->bufferEnabled) {
            return;
        }

        $this->buffer->moveCursorX(-1);
    }

    #[HandlesKey(Key::RIGHT)]
    public function right(): void
    {
        if (! $this->bufferEnabled) {
            return;
        }

        $this->buffer->moveCursorX(1);
    }

    #[HandlesKey(Key::DELETE)]
    public function deleteNextCharacter(): void
    {
        if (! $this->bufferEnabled) {
            return;
        }

        $this->buffer->deleteNextCharacter();
    }

    #[HandlesKey(Key::CTRL_DELETE)]
    public function deleteNextWord(): void
    {
        if (! $this->bufferEnabled) {
            return;
        }

        $this->buffer->deleteNextWord();
    }

    #[HandlesKey(Key::BACKSPACE)]
    public function deletePreviousCharacter(): void
    {
        if (! $this->bufferEnabled) {
            return;
        }

        $this->buffer->deletePreviousCharacter();
    }

    #[HandlesKey(Key::CTRL_BACKSPACE)]
    public function deletePreviousWord(): void
    {
        if (! $this->bufferEnabled) {
            return;
        }

        $this->buffer->deletePreviousWord();
    }
}
