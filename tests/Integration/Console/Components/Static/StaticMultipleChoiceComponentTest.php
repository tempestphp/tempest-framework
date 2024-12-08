<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Components\Static;

use PHPUnit\Framework\Attributes\TestWith;
use Tempest\Console\Console;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class StaticMultipleChoiceComponentTest extends FrameworkIntegrationTestCase
{
    public function test_ask(): void
    {
        $this->console
            ->call(function (Console $console): void {
                $console->ask('test', ['a', 'b', 'c'], multiple: true);
            })
            ->submit('0,1')
            ->assertContains('You picked a and b;');
    }

    public function test_with_invalid_options(): void
    {
        $this->console
            ->call(function (Console $console): void {
                $console->ask('test', ['a', 'b', 'c'], multiple: true);
            })
            ->submit('0,4,c,2')
            ->assertContains('You picked a and c;');
    }

    public function test_confirm(): void
    {
        $this->console
            ->call(function (Console $console): void {
                $answer = $console->ask('test', ['a', 'b', 'c'], multiple: true);

                $console->writeln(json_encode($answer));
            })
            ->submit('0')
            ->assertContains('You picked a;')
            ->submit('no')
            ->assertContains('- [0] a')
            ->submit('0,1')
            ->assertContains('You picked a and b;')
            ->submit('yes')
            ->assertContains('["a","b"]');
    }

    #[TestWith([['a', 'b', 'c'], ['b', 'k'], ['b']])]
    #[TestWith([['foo' => 'foo1', 'bar' => 'bar2'], ['foo', 'baz'], ['foo']])]
    #[TestWith([['foo' => 'foo1', 'bar' => 'bar2'], ['foo1'], []])]
    public function test_supports_defaults_without_prompting(array $options, array $default, array $expected): void
    {
        $this->console
            ->withoutPrompting()
            ->call(function (Console $console) use ($options, $default): void {
                $answer = $console->ask(
                    question: 'test',
                    options: $options,
                    default: $default,
                    multiple: true,
                );

                $console->writeln(json_encode($answer));
            })
            ->assertContains(json_encode($expected));
    }

    public function test_supports_defaults_with_prompting(): void
    {
        $this->console
            ->call(function (Console $console): void {
                $answer = $console->ask(
                    question: 'test',
                    options: ['foo', 'bar'],
                    default: ['foo'],
                    multiple: true,
                );

                $console->writeln(json_encode($answer));
            })
            ->submit()
            ->submit()
            ->assertContains(json_encode(['foo']));
    }
}
