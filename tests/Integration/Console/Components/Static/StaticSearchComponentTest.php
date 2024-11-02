<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Components\Static;

use Tempest\Console\Console;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
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

    public function test_no_answer(): void
    {
        $this->console
            ->call(function (Console $console): void {
                $result = $console->search(
                    label: 'Search',
                    search: $this->search(...),
                );

                $console->write($result ?? '<no answer>');
            })
            ->submit()
            ->assertContains(
                text: <<<TXT
                - [0] Search again
                - [1] Cancel
                TXT,
                ignoreLineEndings: true
            )
            ->submit(1)
            ->assertContains('<no answer>');
    }

    public function test_default_answer(): void
    {
        $this->console
            ->call(function (Console $console): void {
                $result = $console->search(
                    label: 'Search',
                    search: $this->search(...),
                    default: 'foo'
                );

                $console->write($result);
            })
            ->submit()
            ->assertContains(
                text: <<<TXT
                - [0] Search again
                - [1] Cancel
                TXT,
                ignoreLineEndings: true
            )
            ->submit(1)
            ->assertContains('foo');
    }

    public function test_without_prompting(): void
    {
        $this->console
            ->withoutPrompting()
            ->call(function (Console $console): void {
                $result = $console->search(
                    label: 'Search',
                    search: $this->search(...),
                );

                $console->write($result ?? '<no answer>');
            })
            ->assertContains('<no answer>');
    }

    public function test_default_answer_without_prompting(): void
    {
        $this->console
            ->withoutPrompting()
            ->call(function (Console $console): void {
                $result = $console->search(
                    label: 'Search',
                    search: $this->search(...),
                    default: 'foo'
                );

                $console->write($result);
            })
            ->assertContains('foo');
    }

    private function search(?string $query): array
    {
        $data = ['Brent', 'Paul', 'Aidan', 'Roman'];

        if (! $query) {
            return [];
        }

        return array_filter(
            $data,
            fn (string $name) => str_contains(strtolower($name), strtolower($query)),
        );
    }
}
