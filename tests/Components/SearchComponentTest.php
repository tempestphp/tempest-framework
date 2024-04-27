<?php

declare(strict_types=1);

namespace Tests\Tempest\Console\Components;

use Tempest\Console\Components\SearchComponent;
use Tempest\Console\Key;
use Tempest\Console\Point;
use Tests\Tempest\Console\TestCase;

/**
 * @internal
 * @small
 */
class SearchComponentTest extends TestCase
{
    public function test_search_component(): void
    {
        $component = new SearchComponent('Search', $this->search(...));

        $rendered = $component->render();
        $this->assertSame('<question>Search</question> ', $rendered);
        $this->assertSame('Press <em>up</em>/<em>down</em> to select, <em>enter</em> to confirm, <em>ctrl+c</em> to cancel', $component->renderFooter());

        $component->input('a');
        $component->input(Key::UP->value);
        $rendered = $component->render();
        $this->assertStringContainsString('<question>Search</question> a', $rendered);
        $this->assertStringContainsString('[x] <em>Paul</em>', $rendered);
        $this->assertStringContainsString('[ ] Aidan', $rendered);
        $this->assertStringContainsString('[ ] Roman', $rendered);

        $component->up();
        $rendered = $component->render();
        $this->assertStringContainsString('[ ] Paul', $rendered);
        $this->assertStringContainsString('[x] <em>Roman</em>', $rendered);

        $component->down();
        $rendered = $component->render();
        $this->assertStringContainsString('[x] <em>Paul</em>', $rendered);
        $this->assertStringContainsString('[ ] Roman', $rendered);

        $component->input('u');
        $component->input('l');
        $rendered = $component->render();
        $this->assertStringContainsString('<question>Search</question> aul', $rendered);

        $component->backspace();
        $rendered = $component->render();
        $this->assertStringContainsString('<question>Search</question> au', $rendered);

        $component->left();
        $component->input('_');
        $this->assertTrue($component->getCursorPosition()->equals(new Point(11, 0)));

        $component->right();
        $component->right();
        $this->assertTrue($component->getCursorPosition()->equals(new Point(12, 0)));

        $component->input('-');
        $rendered = $component->render();
        $this->assertStringContainsString('<question>Search</question> a_u-', $rendered);

        $component->home();
        $this->assertTrue($component->getCursorPosition()->equals(new Point(9, 0)));

        $component->end();
        $this->assertTrue($component->getCursorPosition()->equals(new Point(13, 0)));

        $component->left();
        $component->left();
        $component->delete();
        $rendered = $component->render();
        $this->assertStringContainsString('<question>Search</question> a_-', $rendered);
        $component->right();

        $this->assertNull($component->enter());

        $component->backspace();
        $component->backspace();
        $component->backspace();
        $component->backspace();
        $component->backspace();
        $component->backspace();
        $this->assertTrue($component->getCursorPosition()->equals(new Point(9, 0)));

        $component->input('P');
        $result = $component->enter();
        $this->assertSame('Paul', $result);
    }

    public function search(string $query): array
    {
        if ($query === '') {
            return [];
        }

        $data = ['Brent', 'Paul', 'Aidan', 'Roman'];

        return array_filter(
            $data,
            fn (string $name) => str_contains(strtolower($name), strtolower($query)),
        );
    }
}
