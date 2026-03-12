<?php

declare(strict_types=1);

namespace Tempest\Console\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Tempest\Console\Components\TextBuffer;

/**
 * @internal
 */
final class TextBufferTest extends TestCase
{
    #[TestWith(['Hello', 5])]
    #[TestWith(['', 0])]
    public function test_construct(string $text, int $cursor): void
    {
        $buffer = new TextBuffer($text);

        $this->assertSame($text, $buffer->text);
        $this->assertSame($cursor, $buffer->cursor);
    }

    #[TestWith(['Hello, world!', 'Bye, cruel world!'])]
    #[TestWith(['', 'hi'])]
    #[TestWith(['', null, ''])]
    #[TestWith(['hi', null, ''])]
    #[TestWith(['hi', 'hi', 'hi'])]
    public function test_set_text(string $initialText, ?string $nextText = null, ?string $expectedText = null): void
    {
        $buffer = new TextBuffer($initialText);
        $buffer->setText($nextText);

        $this->assertSame($expectedText ?? $nextText, $buffer->text);
    }

    #[TestWith([5, 6, 'S', 'Leon Kennedy', 'Leon SKennedy'])]
    #[TestWith([5, 11, 'Scott ', 'Leon Kennedy', 'Leon Scott Kennedy'])]
    #[TestWith([6, 7, '.', 'Leon S', 'Leon S.'])]
    #[TestWith([0, 1, '0', '123', '0123'])]
    public function test_input(int $initialCursor, int $expectedCursor, string $input, string $initialText, string $expectedText): void
    {
        $buffer = new TextBuffer($initialText);

        $buffer->cursor = $initialCursor;
        $buffer->input($input);

        $this->assertSame($expectedText, $buffer->text);
        $this->assertSame($expectedCursor, $buffer->cursor);
    }

    #[TestWith([11, 8, 'foo-bar-baz', 'foo-bar-'])]
    #[TestWith([8, 7, 'foo-bar-', 'foo-bar'])]
    #[TestWith([7, 4, 'foo-bar', 'foo-'])]
    #[TestWith([3, 0, 'foo', ''])]
    #[TestWith([3, 0, '---', ''])]
    #[TestWith([3, 0, '___', ''])]
    #[TestWith([12, 11, 'Hey! Listen!', 'Hey! Listen'])]
    #[TestWith([14, 11, 'My name is Joe', 'My name is '])]
    #[TestWith([7, 5, '$foo = ', '$foo '])]
    public function test_delete_previous_word(int $initialCursor, int $expectedCursor, string $initialText, string $expectedText): void
    {
        $buffer = new TextBuffer($initialText);

        $buffer->cursor = $initialCursor;
        $buffer->deletePreviousWord();

        $this->assertSame($expectedText, $buffer->text);
        $this->assertSame($expectedCursor, $buffer->cursor);
    }

    #[TestWith([0, 0, 'foo-bar-baz', '-bar-baz'])]
    #[TestWith([0, 0, '-bar-baz', 'bar-baz'])]
    #[TestWith([0, 0, 'baz', ''])]
    #[TestWith([0, 0, '___', ''])]
    #[TestWith([0, 0, '---', ''])]
    #[TestWith([0, 0, 'Hey!', '!'])]
    #[TestWith([0, 0, '$foo', 'foo'])]
    #[TestWith([0, 0, 'Jon Doe', ' Doe'])]
    #[TestWith([3, 3, 'foo-bar-baz', 'foobar-baz'])]
    #[TestWith([4, 4, 'foo-bar-baz', 'foo--baz'])]
    #[TestWith([3, 3, 'foo--baz', 'foobaz'])]
    public function test_delete_next_word(int $initialCursor, int $expectedCursor, string $initialText, string $expectedText): void
    {
        $buffer = new TextBuffer($initialText);

        $buffer->cursor = $initialCursor;
        $buffer->deleteNextWord();

        $this->assertSame($expectedText, $buffer->text);
        $this->assertSame($expectedCursor, $buffer->cursor);
    }

    #[TestWith([0, 0, 'abc', 'bc'])]
    #[TestWith([1, 1, 'abc', 'ac'])]
    #[TestWith([2, 2, 'abc', 'ab'])]
    #[TestWith([3, 3, 'abc', 'abc'])]
    #[TestWith([0, 0, '', ''])]
    #[TestWith([0, 0, '-', ''])]
    public function test_delete_next_character(int $initialCursor, int $expectedCursor, string $initialText, string $expectedText): void
    {
        $buffer = new TextBuffer($initialText);

        $buffer->cursor = $initialCursor;
        $buffer->deleteNextCharacter();

        $this->assertSame($expectedText, $buffer->text);
        $this->assertSame($expectedCursor, $buffer->cursor);
    }

    #[TestWith([0, 0, 'abc', 'abc'])]
    #[TestWith([1, 0, 'abc', 'bc'])]
    #[TestWith([2, 1, 'abc', 'ac'])]
    #[TestWith([3, 2, 'abc', 'ab'])]
    #[TestWith([0, 0, '', ''])]
    #[TestWith([1, 0, '-', ''])]
    public function test_delete_previous_character(int $initialCursor, int $expectedCursor, string $initialText, string $expectedText): void
    {
        $buffer = new TextBuffer($initialText);

        $buffer->cursor = $initialCursor;
        $buffer->deletePreviousCharacter();

        $this->assertSame($expectedText, $buffer->text);
        $this->assertSame($expectedCursor, $buffer->cursor);
    }

    #[Test]
    public function test_move_cursor_to_start(): void
    {
        $buffer = new TextBuffer('Hello, world!');
        $buffer->moveCursorX(14);
        $buffer->moveCursorToStart();
        $this->assertSame(0, $buffer->cursor);

        $buffer = new TextBuffer(<<<TXT
            This is a line
            This is a way longer line
            Shorter
            TXT);
        $buffer->moveCursorX(100);
        $buffer->moveCursorToStart();
        $this->assertSame(0, $buffer->cursor);
    }

    #[Test]
    public function test_move_cursor_to_end(): void
    {
        $buffer = new TextBuffer('Hello, world!');
        $buffer->setCursorIndex(0);
        $buffer->moveCursorToEnd();
        $this->assertSame(13, $buffer->cursor);

        $buffer = new TextBuffer(<<<TXT
            This is a line
            This is a way longer line
            Shorter
            TXT);
        $buffer->setCursorIndex(0);
        $buffer->moveCursorToEnd();
        $this->assertSame(48, $buffer->cursor);
    }

    #[Test]
    public function test_move_cursor_to_start_of_line(): void
    {
        $buffer = new TextBuffer('Hello, world!');
        $buffer->setCursorIndex(14);
        $buffer->moveCursorToStartOfLine();
        $this->assertSame(0, $buffer->cursor);
    }

    #[Test]
    #[TestWith([0, 0])]
    #[TestWith([10, 0])]
    #[TestWith([14, 0])]
    #[TestWith([15, 15])]
    #[TestWith([24, 15])]
    #[TestWith([40, 15])]
    #[TestWith([41, 41])]
    #[TestWith([48, 41])]
    public function test_move_cursor_to_start_of_line_multiline(int $initial, int $expected): void
    {
        $buffer = new TextBuffer(<<<TXT
            This is a line
            This is a way longer line
            Shorter
            TXT);

        $buffer->setCursorIndex($initial);
        $buffer->moveCursorToStartOfLine();
        $this->assertSame($expected, $buffer->cursor);
    }

    #[Test]
    public function test_move_cursor_to_end_of_line(): void
    {
        $buffer = new TextBuffer('Hello, world!');
        $buffer->setCursorIndex(0);
        $buffer->moveCursorToEndOfLine();
        $this->assertSame(13, $buffer->cursor);
    }

    #[Test]
    #[TestWith([0, 14])]
    #[TestWith([5, 14])]
    #[TestWith([14, 14])]
    #[TestWith([15, 40])]
    #[TestWith([20, 40])]
    #[TestWith([40, 40])]
    #[TestWith([41, 48])]
    #[TestWith([45, 48])]
    #[TestWith([48, 48])]
    public function test_move_cursor_to_end_of_line_multiline(int $initial, int $expected): void
    {
        $buffer = new TextBuffer(<<<TXT
            This is a line
            This is a way longer line
            Shorter
            TXT);

        $buffer->setCursorIndex($initial);
        $buffer->moveCursorToEndOfLine();
        $this->assertSame($expected, $buffer->cursor);
    }

    #[Test]
    #[TestWith([0, 100, 13])]
    #[TestWith([13, 1, 13])]
    #[TestWith([0, -1, 0])]
    #[TestWith([0, -100, 0])]
    #[TestWith([0, 1, 1])]
    #[TestWith([0, 13, 13])]
    #[TestWith([4, -4, 0])]
    public function test_move_cursor_x(int $initialCursor, int $offsetX, int $expectedPosition): void
    {
        $buffer = new TextBuffer('Hello, world!');

        $buffer->cursor = $initialCursor;
        $buffer->moveCursorX($offsetX);
        $this->assertSame($expectedPosition, $buffer->cursor);
    }

    #[Test]
    #[TestWith([0, 1, 15])]
    #[TestWith([15, 1, 41])]
    #[TestWith([41, 1, 41])]
    #[TestWith([0, -1, 0])]
    #[TestWith([15, -1, 0])]
    #[TestWith([41, -1, 15])]
    #[TestWith([14, 1, 29])]
    #[TestWith([29, 1, 48])]
    #[TestWith([40, 1, 48])]
    #[TestWith([40, -1, 14])]
    public function move_cursor_y(int $initialCursor, int $offsetY, int $expectedPosition): void
    {
        $buffer = new TextBuffer(<<<TXT
            This is a line
            This is a way longer line
            Shorter
            TXT);

        $buffer->setCursorIndex($initialCursor);
        $buffer->moveCursorY($offsetY);
        $this->assertSame($expectedPosition, $buffer->cursor);
    }

    #[TestWith(['123', 0, [0, 0]])]
    #[TestWith(["123\n456", 4, [0, 1]])]
    #[TestWith(["123\n456", 5, [1, 1]])]
    #[TestWith(["123\n\n456", 5, [0, 2]])]
    #[TestWith(["different\nline\nlength", 9, [9, 0]])]
    #[TestWith(["different\nline\nlength", 10, [0, 1]])]
    #[TestWith(["different\nline\nlength", 11, [1, 1]])]
    #[TestWith(["different\nline\nlength", 21, [6, 2]])]
    public function test_relative_cursor_index(string $initialText, int $cursor, array $expectedPoint): void
    {
        $buffer = new TextBuffer($initialText);

        $buffer->cursor = $cursor;

        $point = $buffer->getRelativeCursorPosition();

        $this->assertSame($expectedPoint[0], $point->x);
        $this->assertSame($expectedPoint[1], $point->y);
    }

    #[TestWith(["different\nline\nlength", 5, 0, [0, 0]])]
    #[TestWith(["different\nline\nlength", 5, 9, [4, 1]])]
    #[TestWith(["different\nline\nlength", 5, 13, [3, 2]])]
    #[TestWith(["different\nline\nlength", 5, 21, [1, 4]])]
    public function test_relative_cursor_index_with_wrapping(string $initialText, int $maxLineWidth, int $cursor, array $expectedPoint): void
    {
        $buffer = new TextBuffer($initialText);

        $buffer->cursor = $cursor;

        $point = $buffer->getRelativeCursorPosition($maxLineWidth);

        $this->assertSame($expectedPoint[0], $point->x);
        $this->assertSame($expectedPoint[1], $point->y);
    }
}
