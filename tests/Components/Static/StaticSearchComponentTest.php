<?php

namespace Tests\Tempest\Console\Components\Static;

use Tempest\Console\Console;
use Tests\Tempest\Console\TestCase;

class StaticSearchComponentTest extends TestCase
{
    public function test_search_component(): void
    {
        $this->console
            ->call(function (Console $console) {
                $result = $console->search(
                    label: 'Search',
                    search: $this->search(...),
                );

                $console->success("Hello {$result}");
            })
            ->submit('a')
            ->assertContains(<<<TXT
- [0] Search again
- [1] Paul
- [2] Aidan
- [3] Roman
TXT,
            )
            ->submit(0)
            ->submit('b')
            ->assertContains(<<<TXT
- [0] Search again
- [1] Brent
TXT,
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
