<?php

declare(strict_types=1);

namespace Tests\Tempest\Console\Components;

use PHPUnit\Framework\TestCase;
use Tempest\Console\Components\PasswordComponent;
use Tempest\Console\Key;
use Tempest\Console\Point;
use Tempest\Console\Testing\TestCursor;

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

Press <em>enter</em> to confirm, press <em>ctrl+c</em> to cancel

TXT,
            $component->render(),
        );

        $component->input('a');
        $component->input('b');
        $component->input('c');

        $this->assertStringContainsString("<question>Label</question> ***\n", $component->render());

        $component->input(Key::UP->value);
        $this->assertStringContainsString("<question>Label</question> ***\n", $component->render());

        $component->backspace();
        $this->assertStringContainsString("<question>Label</question> **\n", $component->render());

        $cursor = new TestCursor(1, 1);
        $component->placeCursor($cursor);
        $this->assertTrue($cursor->getPosition()->equals(new Point(11, 1)));

        $password = $component->enter();
        $this->assertSame('ab', $password);
    }
}
