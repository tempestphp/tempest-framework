<?php

declare(strict_types=1);

namespace Tests\Tempest\Console\Components;

use PHPUnit\Framework\TestCase;
use Tempest\Console\Components\PasswordComponent;
use Tempest\Console\Key;
use Tempest\Console\Point;

/**
 * @internal
 * @small
 */
class PasswordComponentTest extends TestCase
{
    public function test_password_component(): void
    {
        $component = new PasswordComponent('Label');

        $this->assertSame(
            <<<'TXT'
            <question>Label</question> 
            TXT,
            $component->render(),
        );

        $component->input('a');
        $component->input('b');
        $component->input('c');

        $this->assertSame("<question>Label</question> ***", $component->render());

        $component->input(Key::UP->value);
        $this->assertSame("<question>Label</question> ***", $component->render());

        $component->backspace();
        $this->assertSame("<question>Label</question> **", $component->render());

        $this->assertTrue($component->getCursorPosition()->equals(new Point(10, 0)));

        $password = $component->enter();
        $this->assertSame('ab', $password);
    }
}
