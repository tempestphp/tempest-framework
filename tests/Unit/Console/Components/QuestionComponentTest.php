<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Console\Components;

use Tempest\Console\Components\Interactive\SingleChoiceComponent;
use Tests\Tempest\Unit\Console\ConsoleIntegrationTestCase;

/**
 * @internal
 * @small
 */
class QuestionComponentTest extends ConsoleIntegrationTestCase
{
    public function test_question_component(): void
    {
        $component = new SingleChoiceComponent('hello', ['a', 'b', 'c']);

        $this->assertSame(0, $component->selectedOption);
        $rendered = $component->render();
        $this->assertStringContainsString('[x] <em>a</em>', $rendered);
        $this->assertStringContainsString('[ ] b', $rendered);
        $this->assertStringContainsString('[ ] c', $rendered);

        $component->down();
        $this->assertSame(1, $component->selectedOption);
        $rendered = $component->render();
        $this->assertStringContainsString('[ ] a', $rendered);
        $this->assertStringContainsString('[x] <em>b</em>', $rendered);
        $this->assertStringContainsString('[ ] c', $rendered);

        $component->down();
        $component->down();
        $this->assertSame(0, $component->selectedOption);

        $component->up();
        $this->assertSame(2, $component->selectedOption);

        $output = $component->enter();
        $this->assertSame('c', $output);
    }
}
