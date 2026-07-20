<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use Tempest\Console\Exceptions\InvalidEnumArgument;
use Tempest\Console\Input\ConsoleArgumentBag;
use Tempest\Console\Input\ConsoleArgumentDefinition;
use Tests\Tempest\Integration\Console\Fixtures\TestStringEnum;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class ConsoleArgumentBagTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function argument_bag_works(): void
    {
        $argv = [
            'tempest',
            'hello:world',
            'value',
            '--force',
            '--times=2',
        ];

        $bag = new ConsoleArgumentBag($argv);

        $this->assertCount(3, $bag->all());

        $firstArg = $bag->all()[0];
        $this->assertSame('value', $firstArg->value);
        $this->assertSame(0, $firstArg->position);
        $this->assertNull($firstArg->name);

        $forceFlag = $bag->all()[1];
        $this->assertTrue($forceFlag->value);
        $this->assertNull($forceFlag->position);
        $this->assertSame('force', $forceFlag->name);

        $timesFlag = $bag->all()[2];
        $this->assertSame('2', $timesFlag->value);
        $this->assertNull($timesFlag->position);
        $this->assertSame('times', $timesFlag->name);

        $this->assertSame(
            'hello:world',
            $bag->getCommandName(),
        );
    }

    #[Test]
    public function positional_vs_named_input(): void
    {
        $this->console
            ->call('complex a --c=c --b=b --flag')
            ->assertContains('abc')
            ->assertContains('true');
    }

    #[Test]
    public function combined_flags(): void
    {
        $this->console
            ->call('flags -ab')
            ->assertContains('ok');
    }

    #[Test]
    public function short_flags_are_mapped_to_parameters_directly(): void
    {
        $this->console
            ->call('flags:short -ab')
            ->assertContains('ok');
    }

    #[Test]
    public function array_input(): void
    {
        $argv = [
            'tempest',
            'test',
            '--input=a',
            '--input=b',
            '--input=c',
        ];

        $bag = new ConsoleArgumentBag($argv);

        $definition = new ConsoleArgumentDefinition(
            name: 'input',
            type: 'array',
            default: null,
            hasDefault: false,
            position: 0,
        );

        $this->assertSame(['a', 'b', 'c'], $bag->findArrayFor($definition)->value);
    }

    #[Test]
    public function array_input_to_command(): void
    {
        $this->console
            ->call('array_input --input=a --input=b')
            ->assertContains('["a","b"]');
    }

    #[Test]
    public function array_with_one_element_to_command(): void
    {
        $this->console
            ->call('array_input --input=a')
            ->assertContains('["a"]');
    }

    #[TestWith(['foo', false])]
    #[TestWith(['bar', true])]
    #[TestWith(['baz', false])]
    #[TestWith(['qux', true])]
    #[TestWith(['bux', true])]
    #[Test]
    public function negative_input(string $name, bool $expected): void
    {
        $argv = [
            'tempest',
            'test',
            '--no-foo',
            '--qux',
            '--bux=true',
            '--no-bar=false',
            '--baz=false',
        ];

        $bag = new ConsoleArgumentBag($argv);

        $definition = new ConsoleArgumentDefinition(
            name: $name,
            type: 'bool',
            default: null,
            hasDefault: false,
            position: 0,
        );

        $this->assertSame($expected, $bag->findFor($definition)->value);
    }

    #[Test]
    public function backed_enum_input(): void
    {
        $argv = [
            'tempest',
            'test',
            '--type=a',
        ];

        $bag = new ConsoleArgumentBag($argv);

        $definition = new ConsoleArgumentDefinition(
            name: 'type',
            type: TestStringEnum::class,
            default: null,
            hasDefault: false,
            position: 0,
        );

        $this->assertSame(TestStringEnum::A, $bag->findFor($definition)->value);
    }

    #[Test]
    public function invalid_backed_enum_input(): void
    {
        $argv = [
            'tempest',
            'test',
            '--type=invalid',
        ];

        $bag = new ConsoleArgumentBag($argv);

        $definition = new ConsoleArgumentDefinition(
            name: 'type',
            type: TestStringEnum::class,
            default: null,
            hasDefault: false,
            position: 0,
        );

        $this->expectException(InvalidEnumArgument::class);
        $bag->findFor($definition);
    }

    #[Test]
    public function name_mapping(): void
    {
        $this->console
            ->call('command-with-argument-name --new-name=foo --new-flag')
            ->assertSee('foo')
            ->assertSee('true');
    }

    #[TestWith(['variadic-string-argument foo bar baz', '["foo","bar","baz"]'])]
    #[TestWith(['variadic-integer-argument 1 2 3', '[1,2,3]'])]
    #[TestWith(['variadic-backed-enum-argument a b c', '["a","b","c"]'])]
    #[Test]
    public function variadic_argument(string $command, string $jsonOutput): void
    {
        $this->console
            ->call($command)
            ->assertSee($jsonOutput);
    }
}
