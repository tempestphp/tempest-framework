<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Components\Static;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Console\Console;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class StaticSearchComponentTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function search_component(): void
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
                - [0] Cancel
                - [1] Search again
                - [2] Paul
                - [3] Aidan
                - [4] Roman
                TXT,
                true,
            )
            ->submit(1)
            ->submit('b')
            ->assertContains(
                <<<TXT
                - [0] Cancel
                - [1] Search again
                - [2] Brent
                TXT,
                true,
            )
            ->submit(2)
            ->assertContains('Hello Brent');
    }

    #[Test]
    public function no_answer(): void
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
                - [0] Cancel
                - [1] Search again
                TXT,
                ignoreLineEndings: true,
            )
            ->submit(0)
            ->assertContains('<no answer>');
    }

    #[Test]
    public function default_answer(): void
    {
        $this->console
            ->call(function (Console $console): void {
                $result = $console->search(
                    label: 'Search',
                    search: $this->search(...),
                    default: 'foo',
                );

                $console->write($result);
            })
            ->submit()
            ->assertContains(
                text: <<<TXT
                - [0] Cancel
                - [1] Search again
                TXT,
                ignoreLineEndings: true,
            )
            ->submit(0)
            ->assertContains('foo');
    }

    #[Test]
    public function without_prompting(): void
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

    #[Test]
    public function default_answer_without_prompting(): void
    {
        $this->console
            ->withoutPrompting()
            ->call(function (Console $console): void {
                $result = $console->search(
                    label: 'Search',
                    search: $this->search(...),
                    default: 'foo',
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
