<?php

namespace Components;

use Tempest\Console\Components\TextBoxComponent;
use Tempest\Console\Point;
use Tests\Tempest\Console\TestCase;

class TextBoxComponentTest extends TestCase
{
    public function test_text_question_component(): void
    {
        $component = new TextBoxComponent('q');

        $this->assertTrue($component->getCursorPosition()->equals(new Point(2, 1)));

        $component->input('a');
        $this->assertTrue($component->getCursorPosition()->equals(new Point(3, 1)));

        $component->input('b');
        $component->input('c');
        $component->input("\e[A");
        $this->assertSame('abc', $component->answer);
        $this->assertTrue($component->getCursorPosition()->equals(new Point(5, 1)));
        $this->assertStringContainsString('abc', $component->render());

        $component->left();
        $this->assertTrue($component->getCursorPosition()->equals(new Point(4, 1)));

        $component->right();
        $this->assertTrue($component->getCursorPosition()->equals(new Point(5, 1)));

        $component->up();
        $this->assertTrue($component->getCursorPosition()->equals(new Point(2, 1)));

        $component->down();
        $this->assertTrue($component->getCursorPosition()->equals(new Point(5, 1)));

        $component->backspace();
        $this->assertTrue($component->getCursorPosition()->equals(new Point(4, 1)));
        $this->assertSame('ab', $component->answer);

        $component->left();
        $component->delete();
        $this->assertSame('a', $component->answer);
        $this->assertTrue($component->getCursorPosition()->equals(new Point(3, 1)));

        $result = $component->enter();
        $this->assertSame('a', $result);
    }
}
