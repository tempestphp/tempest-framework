<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Components;

use PHPUnit\Framework\TestCase;
use Tempest\Console\Components\Interactive\ConfirmComponent;
use Tempest\Console\Key;
use Tempest\Console\Point;

/**
 * @internal
 */
final class ConfirmComponentTest extends TestCase
{
    public function test_confirm_component(): void
    {
        $component = new ConfirmComponent('Label', default: true);

        $this->assertSame(
            '<question>Label</question> [<em><u>yes</u></em>/no] ',
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

        $this->assertTrue($component->getCursorPosition()->equals(new Point(17, 0)));
    }
}
