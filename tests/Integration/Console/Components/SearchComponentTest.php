<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Components;

use Tempest\Console\Components\Interactive\SearchComponent;
use Tempest\Console\Console;
use Tempest\Console\Terminal\Terminal;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class SearchComponentTest extends FrameworkIntegrationTestCase
{
    public function test_single(): void
    {
        $this->console->withoutPrompting()->call(function (Console $console) {
            $terminal = new Terminal($console);
            $component = new SearchComponent(label: 'Enter a name', search: $this->search(...), multiple: false);

            $this->assertStringContainsString('Enter a name', $component->render($terminal));

            $this->assertStringContainsString('Brent', $component->render($terminal));
            $this->assertStringContainsString('Paul', $component->render($terminal));
            $this->assertStringContainsString('Aidan', $component->render($terminal));
            $this->assertStringContainsString('Roman', $component->render($terminal));

            $component->input('a');

            $this->assertStringNotContainsString('Brent', $component->render($terminal));
            $this->assertStringContainsString('Paul', $component->render($terminal));
            $this->assertStringContainsString('Aidan', $component->render($terminal));
            $this->assertStringContainsString('Roman', $component->render($terminal));

            $component->deletePreviousCharacter();
            $component->input('n');

            $this->assertStringContainsString('Brent', $component->render($terminal));
            $this->assertStringContainsString('Aidan', $component->render($terminal));
            $this->assertStringContainsString('Roman', $component->render($terminal));

            $component->down();

            $this->assertSame('Aidan', $component->enter());
        });
    }

    public function test_multiple(): void
    {
        $this->console->withoutPrompting()->call(function (Console $console) {
            $component = new SearchComponent(label: 'Enter a name', search: $this->search(...), multiple: true);

            $component->input(' ');
            $component->down();
            $component->input(' ');

            $this->assertSame(['Brent', 'Paul'], $component->enter());
        });
    }

    public function test_multiple_with_filtering(): void
    {
        $this->console->withoutPrompting()->call(function () {
            $component = new SearchComponent(label: 'Enter a name', search: $this->search(...), multiple: true);

            $component->input('/');
            $component->input('P');
            $component->input('a');
            $component->stopFiltering();
            $component->input(' ');

            $this->assertSame(['Paul'], $component->enter());
        });

        $this->console->withoutPrompting()->call(function () {
            $component = new SearchComponent(label: 'Enter a name', search: $this->search(...), multiple: true);

            $component->input('/');
            $component->input('B');
            $component->input('r');
            $component->stopFiltering();
            $component->input(' ');

            $component->input('/');
            $component->deletePreviousWord();
            $component->input('P');
            $component->input('a');
            $component->stopFiltering();
            $component->input(' ');

            $this->assertSame(['Paul'], $component->enter());
        });
    }

    // public function test_searching_does_not_clear_active(): void
    // {
    //     $this->console->withoutPrompting()->call(function () {
    //         $component = new SearchComponent(label: 'Enter a name', search: $this->search(...), multiple: true);

    //         $component->down();
    //         $component->down();
    //         $component->input('/');
    //         $component->input('a');

    //         $this->assertSame(['Aidan'], $component->enter());
    //     });
    // }

    public function test_multiple_supports_default_value(): void
    {
        $this->console->withoutPrompting()->call(function (Console $console) {
            $terminal = new Terminal($console);
            $component = new SearchComponent(label: 'Enter a name', search: $this->search(...), multiple: true, default: 'Aidan');

            $this->assertStringContainsString('Enter a name', $component->render($terminal));
            $component->enter();

            $this->assertSame(['Aidan'], $component->enter());
        });
    }

    public function search(string $query): array
    {
        $data = ['Brent', 'Paul', 'Aidan', 'Roman'];

        if ($query === '') {
            return $data;
        }

        return array_filter(
            $data,
            fn (string $name) => str_contains(strtolower($name), strtolower($query)),
        );
    }
}
