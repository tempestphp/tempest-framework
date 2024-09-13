<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Components\Static;

use Tempest\Console\Console;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
 */
final class StaticSearchComponentTest extends FrameworkIntegrationTestCase
{
    public function test_search_component(): void
    {
        $this->console
            ->call(function (Console $console): void {
                $result = $console->search(
                    label: 'Search',
                    search: $this->search(...),
                );

                $console->success("Hello {$result}");
            })
            ->submit('a')
            ->assertContains(
                <<<TXT
- [0] Search again
- [1] Paul
- [2] Aidan
- [3] Roman
TXT,
                true
            )
            ->submit(0)
            ->submit('b')
            ->assertContains(
                <<<TXT
- [0] Search again
- [1] Brent
TXT,
                true
            )
            ->submit(1)
            ->assertContains("Hello Brent");
    }

    private function search(string $query): array
    {
        $data = ['Brent', 'Paul', 'Aidan', 'Roman'];

        if ($query === '') {
            return [];
        }

        return array_filter(
            $data,
            fn (string $name) => str_contains(strtolower($name), strtolower($query)),
        );
    }
}
