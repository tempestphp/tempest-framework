<?php

declare(strict_types=1);

namespace Tempest\Console\Components;

use Tempest\Console\Point;

use function Tempest\Support\str;

/**
 * Offers the ability to manipulate a string based on a cursor position.
 */
final class TextBuffer
{
    public function __construct(
        public ?string $text = '',
        public int $cursor = 0,
    ) {
        $this->setText($text);
    }

    public function setText(?string $text): void
    {
        $this->text = str_replace("\r\n", "\n", $text ?? '');
        $this->cursor = mb_strlen($this->text);
    }

    public function input(string $key): void
    {
        if (str_starts_with($key, "\e")) {
            return;
        }

        $this->text = str($this->text)
            ->insertAt($this->cursor, str_replace("\r\n", "\n", $key))
            ->toString();

        $this->moveCursorX(mb_strlen($key));
    }

    public function deleteNextCharacter(): void
    {
        if ($this->cursor === mb_strlen($this->text)) {
            return;
        }

        $this->text = str($this->text)
            ->replaceAt($this->cursor, 1, '')
            ->toString();
    }

    public function deletePreviousCharacter(): void
    {
        if ($this->cursor === 0) {
            return;
        }

        $this->text = str($this->text)
            ->replaceAt($this->cursor, -1, '')
            ->toString();

        $this->moveCursorX(-1);
    }

    public function moveCursorToPreviousWord(): void
    {
        if ($this->cursor === 0) {
            return;
        }

        $end = $this->cursor;
        $pos = $end - 1;

        // Ignore whitespace
        while ($pos >= 0 && $this->isWhitespace($this->text[$pos])) {
            $pos--;
        }

        // If we started on a word character, keep going until we hit non-word
        if ($pos >= 0 && $this->isAlphaNumeric($this->text[$pos])) {
            while ($pos >= 0 && $this->isAlphaNumeric($this->text[$pos])) {
                $pos--;
            }

            $pos++; // Move back to include the first character of the word
        } elseif ($pos >= 0) { // If we started on a non-word character, we delete symbols
            while ($pos >= 0 && $this->isSymbol($this->text[$pos])) {
                $pos--;
            }

            $pos++; // Move back to include the first character of the word
        }

        $this->cursor = $pos;
    }

    public function deletePreviousWord(): void
    {
        $previousCursor = $this->cursor;

        $this->moveCursorToPreviousWord();

        $this->text = substr($this->text, 0, $this->cursor) . substr($this->text, $previousCursor);
    }

    public function moveCursorToNextWord(): void
    {
        if ($this->cursor >= mb_strlen($this->text)) {
            return;
        }

        $start = $this->cursor;
        $pos = $start;

        // Skip leading whitespace
        while ($pos < mb_strlen($this->text) && $this->isWhitespace($this->text[$pos])) {
            $pos++;
        }

        // If we start on a word, keep going until we hit non-word
        if ($pos < mb_strlen($this->text) && $this->isAlphaNumeric($this->text[$pos])) {
            while ($pos < mb_strlen($this->text) && $this->isAlphaNumeric($this->text[$pos])) {
                $pos++;
            }
        } elseif ($pos < mb_strlen($this->text)) { // If we started on a non-word character, just delete that
            while ($pos < mb_strlen($this->text) && $this->isSymbol($this->text[$pos])) {
                $pos++;
            }
        }

        $this->cursor = $pos;
    }

    public function deleteNextWord(): void
    {
        $previousCursor = $this->cursor;

        $this->moveCursorToNextWord();

        $this->text = substr($this->text, 0, $previousCursor) . substr($this->text, $this->cursor);
        $this->cursor = $previousCursor;
    }

    public function setCursorIndex(int $index): void
    {
        $this->cursor = min(max(0, $index), mb_strlen($this->text ?? ''));
    }

    public function moveCursorX(int $offset): void
    {
        $this->setCursorIndex($this->cursor + $offset);
    }

    public function moveCursorY(int $offset): void
    {
        if ($offset === 0 || ! $this->text) {
            return;
        }

        $lines = $this->getLines();
        $linePositions = $this->getLinePositions();
        $currentLineIndex = $this->getCurrentLineIndex();
        $targetLineIndex = max(0, min($currentLineIndex + $offset, count($lines) - 1));

        // If we didn't actually move, return early
        if ($targetLineIndex === $currentLineIndex) {
            return;
        }

        $xOffset = $this->cursor - $linePositions[$currentLineIndex];
        $newPosition = $linePositions[$targetLineIndex] + min($xOffset, mb_strlen($lines[$targetLineIndex]));

        $this->setCursorIndex($newPosition);
    }

    public function moveCursorToStartOfLine(): void
    {
        if (! $this->text) {
            return;
        }

        $linePositions = $this->getLinePositions();
        $currentLineIndex = $this->getCurrentLineIndex();

        $this->setCursorIndex($linePositions[$currentLineIndex]);
    }

    public function moveCursorToEndOfLine(): void
    {
        if (! $this->text) {
            return;
        }

        $lines = $this->getLines();
        $linePositions = $this->getLinePositions();
        $currentLineIndex = $this->getCurrentLineIndex();

        $currentLine = $lines[$currentLineIndex];
        $lineStart = $linePositions[$currentLineIndex];

        $this->setCursorIndex($lineStart + mb_strlen($currentLine));
    }

    public function moveCursorToStart(): void
    {
        $this->setCursorIndex(0);
    }

    public function moveCursorToEnd(): void
    {
        $this->setCursorIndex(mb_strlen($this->text));
    }

    // This method returns the X and Y coordinates of the cursor, relative to the text only.
    // The difficulty resides in the fact that some lines are wrapped (due to `$maxLineCharacters`)
    // and some lines are simply using `\n`, resulting in potential one-off cursor positioning errors.
    // Good luck refactoring that!
    public function getRelativeCursorPosition(?int $maxLineCharacters = null): Point
    {
        $cursorPosition = $this->cursor;
        $lines = str($this->text ?? '')->explode("\n");

        $yPosition = 0;
        $xPosition = 0;
        $lineIndex = 0;
        $charIndex = 0;

        foreach ($lines as $line) {
            $splitLines = str($line)->chunk($maxLineCharacters ?? mb_strlen($line))->toArray();

            foreach ($splitLines as $splitLineIndex => $splitLine) {
                $lineLength = mb_strlen($splitLine);

                // If the cursor is within this line, update the x position and return.
                if (($charIndex + $lineLength) >= $cursorPosition) {
                    $xPosition = $cursorPosition - $charIndex;

                    return new Point($xPosition, $yPosition);
                }

                // If the cursor is not within this line, update the character index and y position.
                $charIndex += $lineLength;

                // If this is not the last split line, increment the y position.
                if ($splitLineIndex < (count($splitLines) - 1)) {
                    $yPosition++;
                }
            }

            // If this is not the last line, increment the y position and reset the character index.
            if ($lineIndex < (count($lines) - 1)) {
                $yPosition++;
                $charIndex += 1; // Account for the newline character
            }

            $lineIndex++;
        }

        // If the cursor is beyond the last line, set the x position to the length of the last line.
        $xPosition = str($lines->last())->length();

        return new Point($xPosition, $yPosition);
    }

    private function getLines(): array
    {
        return str($this->text)->explode("\n")->toArray();
    }

    private function getLinePositions(): array
    {
        $lines = $this->getLines();
        $positions = [];
        $position = 0;

        foreach ($lines as $line) {
            $positions[] = $position;
            $position += mb_strlen($line) + 1; // +1 for newline
        }

        return $positions;
    }

    private function getCurrentLineIndex(): int
    {
        $linePositions = $this->getLinePositions();

        foreach ($linePositions as $index => $startPosition) {
            $nextPosition = ($index + 1) < count($linePositions)
                ? $linePositions[$index + 1]
                : (mb_strlen($this->text) + 1);

            if ($this->cursor >= $startPosition && $this->cursor < $nextPosition) {
                return $index;
            }
        }

        return count($linePositions) - 1; // Default to last line if not found
    }

    private function isWhitespace(string $char): bool
    {
        return preg_match('/\s/', $char) === 1;
    }

    private function isAlphaNumeric(string $char): bool
    {
        return preg_match('/[\w]/', $char) === 1;
    }

    private function isSymbol(string $char): bool
    {
        return preg_match('/[^\w\s]/', $char) === 1;
    }
}
