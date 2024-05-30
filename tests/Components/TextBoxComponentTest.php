<?php

declare(strict_types=1);

namespace Tests\Tempest\Console\Components;

use Tempest\Console\Components\Interactive\TextBoxComponent;
use Tempest\Console\Point;
use Tests\Tempest\Console\ConsoleIntegrationTestCase;

/**
 * @internal
 * @small
 */
class TextBoxComponentTest extends ConsoleIntegrationTestCase
{
    public function test_text_question_component(): void
    {
        $component = new TextBoxComponent('q');
        $this->assertTrue($component->cursorPosition->equals(new Point(2, 1)));

        $component->backspace();
        $this->assertTrue($component->cursorPosition->equals(new Point(2, 1)));

        $component->input('a');
        $this->assertTrue($component->cursorPosition->equals(new Point(3, 1)));

        $component->input('b');
        $component->input('c');
        $component->input("\e[A");
        $this->assertSame('abc', $component->answer);
        $this->assertTrue($component->cursorPosition->equals(new Point(5, 1)));
        $this->assertStringContainsString('abc', $component->render());

        $component->left();
        $this->assertTrue($component->cursorPosition->equals(new Point(4, 1)));

        $component->right();
        $this->assertTrue($component->cursorPosition->equals(new Point(5, 1)));

        $component->home();
        $this->assertTrue($component->cursorPosition->equals(new Point(2, 1)));

        $component->end();
        $this->assertTrue($component->cursorPosition->equals(new Point(5, 1)));

        $component->backspace();
        $this->assertTrue($component->cursorPosition->equals(new Point(4, 1)));
        $this->assertSame('ab', $component->answer);

        $component->left();
        $component->input('_');
        $rendered = $component->render();
        $this->assertStringContainsString('a_b', $rendered);

        $component->backspace();
        $component->right();

        $component->left();
        $component->delete();
        $this->assertSame('a', $component->answer);
        $this->assertTrue($component->cursorPosition->equals(new Point(3, 1)));

        $this->assertTrue($component->getCursorPosition()->equals(new Point(5, 0)));

        $result = $component->enter();
        $this->assertSame('a', $result);
    }
}
