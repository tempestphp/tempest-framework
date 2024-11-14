<?php

declare(strict_types=1);

namespace Tempest\Console\Components\Concerns;

use Tempest\Console\Components\TextBuffer;

trait HasTextBuffer
{
    public TextBuffer $buffer;

    public function setText(?string $text): void
    {
        $this->buffer->setText($text);
    }

    public function getText(): ?string
    {
        return $this->buffer->text;
    }

    public function input(string $key): void
    {
        $this->buffer->input($key);
    }

    public function deletePreviousCharacter(): void
    {
        $this->buffer->deletePreviousCharacter();
    }

    public function deleteNextCharacter(): void
    {
        $this->buffer->deleteNextCharacter();
    }

    public function deletePreviousWord(): void
    {
        $this->buffer->deletePreviousWord();
    }

    public function deleteNextWord(): void
    {
        $this->buffer->deleteNextWord();
    }

    public function setCursorIndex(int $index): void
    {
        $this->buffer->setCursorIndex($index);
    }

    public function moveCursorX(int $offset): void
    {
        $this->buffer->moveCursorX($offset);
    }

    public function moveCursorY(int $offset): void
    {
        $this->buffer->moveCursorY($offset);
    }

    public function moveCursorToStart(): void
    {
        $this->buffer->moveCursorToStart();
    }

    public function moveCursorToEnd(): void
    {
        $this->buffer->moveCursorToEnd();
    }

    public function moveCursorToNextWord(): void
    {
        $this->buffer->moveCursorToNextWord();
    }

    public function moveCursorToPreviousWord(): void
    {
        $this->buffer->moveCursorToPreviousWord();
    }

    public function moveCursorToStartOfLine(): void
    {
        $this->buffer->moveCursorToStartOfLine();
    }

    public function moveCursorToEndOfLine(): void
    {
        $this->buffer->moveCursorToEndOfLine();
    }
}
