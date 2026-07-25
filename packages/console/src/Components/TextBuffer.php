<?php

declare(strict_types=1);

namespace Tempest\Console\Components;

use Tempest\Console\Point;

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
        $this->cursor = $this->getGraphemeLength($this->text);
    }

    public function input(string $key): void
    {
        if (str_starts_with($key, "\e")) {
            return;
        }

        $input = str_replace("\r\n", "\n", $key);

        $this->replaceGraphemes($this->cursor, 0, $input);
        $this->moveCursorX($this->getGraphemeLength($input));
    }

    public function deleteNextCharacter(): void
    {
        if ($this->cursor === $this->getGraphemeLength($this->text)) {
            return;
        }

        $this->replaceGraphemes($this->cursor, 1);
    }

    public function deletePreviousCharacter(): void
    {
        if ($this->cursor === 0) {
            return;
        }

        $this->replaceGraphemes($this->cursor - 1, 1);
        $this->moveCursorX(-1);
    }

    public function deleteCurrentLine(): void
    {
        $lines = $this->getLines();
        $linePositions = $this->getLinePositions();
        $currentLineIndex = $this->getCurrentLineIndex();
        $lineStart = $linePositions[$currentLineIndex];
        $lineLength = $this->getGraphemeLength($lines[$currentLineIndex]);

        $this->replaceGraphemes($lineStart, $lineLength);
        $this->cursor = $lineStart;
    }

    public function moveCursorToPreviousWord(): void
    {
        if ($this->cursor === 0) {
            return;
        }

        $graphemes = $this->getGraphemes($this->text);
        $position = $this->cursor - 1;

        while ($position >= 0 && $this->isWhitespace($graphemes[$position])) {
            $position--;
        }

        if ($position >= 0 && $this->isAlphaNumeric($graphemes[$position])) {
            while ($position >= 0 && $this->isAlphaNumeric($graphemes[$position])) {
                $position--;
            }
        } elseif ($position >= 0) {
            while ($position >= 0 && $this->isSymbol($graphemes[$position])) {
                $position--;
            }
        }

        $this->cursor = $position + 1;
    }

    public function deletePreviousWord(): void
    {
        $previousCursor = $this->cursor;

        $this->moveCursorToPreviousWord();
        $this->replaceGraphemes($this->cursor, $previousCursor - $this->cursor);
    }

    public function moveCursorToNextWord(): void
    {
        $graphemes = $this->getGraphemes($this->text);
        $length = count($graphemes);

        if ($this->cursor >= $length) {
            return;
        }

        $position = $this->cursor;

        while ($position < $length && $this->isWhitespace($graphemes[$position])) {
            $position++;
        }

        if ($position < $length && $this->isAlphaNumeric($graphemes[$position])) {
            while ($position < $length && $this->isAlphaNumeric($graphemes[$position])) {
                $position++;
            }
        } elseif ($position < $length) {
            while ($position < $length && $this->isSymbol($graphemes[$position])) {
                $position++;
            }
        }

        $this->cursor = $position;
    }

    public function deleteNextWord(): void
    {
        $previousCursor = $this->cursor;

        $this->moveCursorToNextWord();
        $this->replaceGraphemes($previousCursor, $this->cursor - $previousCursor);
        $this->cursor = $previousCursor;
    }

    public function setCursorIndex(int $index): void
    {
        $this->cursor = min(max(0, $index), $this->getGraphemeLength($this->text));
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
        $newPosition = $linePositions[$targetLineIndex] + min($xOffset, $this->getGraphemeLength($lines[$targetLineIndex]));

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

        $this->setCursorIndex($lineStart + $this->getGraphemeLength($currentLine));
    }

    public function moveCursorToStart(): void
    {
        $this->setCursorIndex(0);
    }

    public function moveCursorToEnd(): void
    {
        $this->setCursorIndex($this->getGraphemeLength($this->text));
    }

    public function getRelativeCursorPosition(?int $maxLineCharacters = null): Point
    {
        $x = 0;
        $y = 0;

        foreach (array_slice($this->getGraphemes($this->text), 0, $this->cursor) as $grapheme) {
            if ($grapheme === "\n") {
                $x = 0;
                $y++;

                continue;
            }

            $width = $this->getGraphemeWidth($grapheme);

            if ($maxLineCharacters !== null && $x > 0 && ($x + $width) > $maxLineCharacters) {
                $x = 0;
                $y++;
            }

            $x += $width;
        }

        return new Point($x, $y);
    }

    /**
     * @return list<string>
     */
    public function getWrappedLines(int $maximumWidth, ?string $fallback = null): array
    {
        $maximumWidth = max(1, $maximumWidth);
        $wrappedLines = [];

        foreach (explode("\n", $this->text ?: $fallback ?? '') as $line) {
            $wrappedLine = '';
            $wrappedLineWidth = 0;

            foreach ($this->getGraphemes($line) as $grapheme) {
                $width = $this->getGraphemeWidth($grapheme);

                if ($wrappedLine !== '' && ($wrappedLineWidth + $width) > $maximumWidth) {
                    $wrappedLines[] = $wrappedLine;
                    $wrappedLine = '';
                    $wrappedLineWidth = 0;
                }

                $wrappedLine .= $grapheme;
                $wrappedLineWidth += $width;
            }

            $wrappedLines[] = $wrappedLine;
        }

        return $wrappedLines;
    }

    /** @return list<string> */
    private function getLines(): array
    {
        return explode("\n", $this->text ?? '');
    }

    /** @return list<int> */
    private function getLinePositions(): array
    {
        $lines = $this->getLines();
        $positions = [];
        $position = 0;

        foreach ($lines as $line) {
            $positions[] = $position;
            $position += $this->getGraphemeLength($line) + 1;
        }

        return $positions;
    }

    private function getCurrentLineIndex(): int
    {
        $linePositions = $this->getLinePositions();

        foreach ($linePositions as $index => $startPosition) {
            $nextPosition = ($index + 1) < count($linePositions)
                ? $linePositions[$index + 1]
                : $this->getGraphemeLength($this->text) + 1;

            if ($this->cursor >= $startPosition && $this->cursor < $nextPosition) {
                return $index;
            }
        }

        return count($linePositions) - 1; // Default to last line if not found
    }

    private function isWhitespace(string $grapheme): bool
    {
        return preg_match('/^\s+$/u', $grapheme) === 1;
    }

    private function isAlphaNumeric(string $grapheme): bool
    {
        return preg_match('/[\p{L}\p{N}_]/u', $grapheme) === 1;
    }

    private function isSymbol(string $grapheme): bool
    {
        return ! $this->isWhitespace($grapheme) && ! $this->isAlphaNumeric($grapheme);
    }

    /** @return list<string> */
    private function getGraphemes(?string $text): array
    {
        /** @var array{0: list<string>} $matches */
        $matches = [];
        preg_match_all('/\X/u', $text ?? '', $matches);

        return $matches[0];
    }

    private function getGraphemeLength(?string $text): int
    {
        return count($this->getGraphemes($text));
    }

    private function getGraphemeWidth(string $grapheme): int
    {
        $hasEmojiPresentation = preg_match('/[\p{Emoji_Presentation}\x{20E3}]/u', $grapheme) === 1;
        $hasEmojiVariation = preg_match('/\p{Extended_Pictographic}\x{FE0F}/u', $grapheme) === 1;

        if ($hasEmojiPresentation || $hasEmojiVariation) {
            return 2;
        }

        $printable = preg_replace('/[\p{M}\p{Cf}\p{Emoji_Modifier}]/u', '', $grapheme);

        return mb_strwidth($printable ?? $grapheme, 'UTF-8');
    }

    private function replaceGraphemes(int $position, int $length, string $replacement = ''): void
    {
        $graphemes = $this->getGraphemes($this->text);

        array_splice($graphemes, $position, $length, $this->getGraphemes($replacement));

        $this->text = implode('', $graphemes);
    }
}
