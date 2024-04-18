<?php

declare(strict_types=1);

namespace Tests\Tempest\Console\Components;

use Tempest\Console\Components\TextBoxComponent;
use Tempest\Console\Point;
use Tempest\Console\Testing\TestCursor;
use Tests\Tempest\Console\TestCase;

/**
 * @internal
 * @small
 */
class TextBoxComponentTest extends TestCase
{
    public function test_text_question_component(): void
    {
        $component = new TextBoxComponent('q');
        $this->assertTrue($component->componentCursorPosition->equals(new Point(2, 1)));

        $component->backspace();
        $this->assertTrue($component->componentCursorPosition->equals(new Point(2, 1)));

        $component->input('a');
        $this->assertTrue($component->componentCursorPosition->equals(new Point(3, 1)));

        $component->input('b');
        $component->input('c');
        $component->input("\e[A");
        $this->assertSame('abc', $component->answer);
        $this->assertTrue($component->componentCursorPosition->equals(new Point(5, 1)));
        $this->assertStringContainsString('abc', $component->render());

        $component->left();
        $this->assertTrue($component->componentCursorPosition->equals(new Point(4, 1)));

        $component->right();
        $this->assertTrue($component->componentCursorPosition->equals(new Point(5, 1)));

        $component->up();
        $this->assertTrue($component->componentCursorPosition->equals(new Point(2, 1)));

        $component->down();
        $this->assertTrue($component->componentCursorPosition->equals(new Point(5, 1)));

        $component->backspace();
        $this->assertTrue($component->componentCursorPosition->equals(new Point(4, 1)));
        $this->assertSame('ab', $component->answer);

        $component->left();
        $component->delete();
        $this->assertSame('a', $component->answer);
        $this->assertTrue($component->componentCursorPosition->equals(new Point(3, 1)));

        $cursor = new TestCursor();
        $component->placeCursor($cursor);
        $this->assertTrue($cursor->getPosition()->equals(new Point(6, 1)));

        $result = $component->enter();
        $this->assertSame('a', $result);
    }
}
