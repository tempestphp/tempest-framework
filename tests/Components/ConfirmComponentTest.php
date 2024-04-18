<?php

declare(strict_types=1);

namespace Tests\Tempest\Console\Components;

use PHPUnit\Framework\TestCase;
use Tempest\Console\Components\ConfirmComponent;
use Tempest\Console\Key;
use Tempest\Console\Point;
use Tempest\Console\Testing\TestCursor;

/**
 * @internal
 * @small
 */
class ConfirmComponentTest extends TestCase
{
    public function test_confirm_component(): void
    {
        $component = new ConfirmComponent('Label', default: true);

        $this->assertSame(
            <<<'TXT'
            <question>Label</question> [<em><u>yes</u></em>/no] 
            Press <em>enter</em> to confirm
            TXT,
            $component->render(),
        );

        $component->input('a');
        $component->input(Key::UP->value);
        $this->assertStringContainsString('<question>Label</question> [<em><u>yes</u></em>/no]', $component->render());

        $component->toggle();
        $this->assertStringContainsString('<question>Label</question> [yes/<em><u>no</u></em>]', $component->render());

        $component->input('a');
        $component->input(Key::UP->value);
        $this->assertStringContainsString('<question>Label</question> [yes/<em><u>no</u></em>]', $component->render());

        $component->input('y');
        $this->assertStringContainsString('<question>Label</question> [<em><u>yes</u></em>/no] y', $component->render());

        $component->toggle();
        $this->assertStringContainsString('<question>Label</question> [yes/<em><u>no</u></em>] n', $component->render());

        $this->assertFalse($component->enter());

        $cursor = new TestCursor(1, 1);
        $component->placeCursor($cursor);
        $this->assertTrue($cursor->getPosition()->equals(new Point(2, 1)));
    }
}
