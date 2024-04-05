<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Console;

use PHPUnit\Framework\TestCase;
use Tempest\Console\Console;
use Tempest\Console\ConsoleOutputBuilder;
use Tempest\Console\ConsoleOutputType;
use Tempest\Console\GenericConsoleOutput;

/**
 * @internal
 * @small
 */
class ConsoleOutputBuilderTest extends TestCase
{
    private function newBuilder(): ConsoleOutputBuilder
    {
        return new ConsoleOutputBuilder(
            new GenericConsoleOutput()
        );
    }

    public function test_it_adds_lines(): void
    {
        $builder = $this->newBuilder();

        $builder->add('test', ConsoleOutputType::Brand);
        $builder->add('test 2', ConsoleOutputType::Error);
        $builder->blank()
            ->info('test 3')
            ->warning('test 4')
            ->success('test 5')
            ->muted('test 6')
            ->comment('test 7')
            ->raw('test 8');

        $this->assertCount(9, $builder->getLines());

        $this->assertSame('test', $builder->getLines()[0]->line);
        $this->assertSame(ConsoleOutputType::Brand, $builder->getLines()[0]->type);

        $this->assertSame('test 2', $builder->getLines()[1]->line);
        $this->assertSame(ConsoleOutputType::Error, $builder->getLines()[1]->type);

        $this->assertSame('test 8', $builder->getLines()[8]->line);
        $this->assertSame(ConsoleOutputType::Formatted, $builder->getLines()[8]->type);
    }

    public function test_rendering_works(): void
    {
        $builder = $this->newBuilder();
        $builder->add(['a', 'b', 'c']);

        $this->assertSame('a' . PHP_EOL . 'b' . PHP_EOL . 'c', $builder->toString());
    }

    public function test_changing_glue_works(): void
    {
        $builder = $this->newBuilder()->glueWith(' ');
        $builder->add(['a', 'b', 'c']);

        $this->assertSame("a b c", $builder->toString());

        $builder = $this->newBuilder()->glueWith('-');
        $builder->add(['a', 'b', 'c']);

        $this->assertSame("a-b-c", $builder->toString());
    }

    public function test_multiline_formats_are_working(): void
    {
        $builder = $this->newBuilder();

        $builder->comments(['a', 'b', 'c']);

        $this->assertSame("/**" . PHP_EOL . "* a" . PHP_EOL . "* b" . PHP_EOL . "* c" . PHP_EOL . "*/", $builder->toString(format: false));
    }

    public function test_write_clears_lines(): void
    {
        $console = $this->createMock(Console::class);
        $console->expects($this->exactly(2))->method('write');

        $builder = $this->newBuilder();
        $builder->glueWith(' ');

        $builder->add(['a', 'b', 'c']);

        $builder->write($console);

        $this->assertCount(0, $builder->getLines());

        $builder->add(['a', 'b']);

        $this->assertCount(2, $builder->getLines());

        $builder->write($console);

        $this->assertCount(0, $builder->getLines());
    }

    public function nesting_works(): void
    {
        $builder = $this->newBuilder();

        $builder->nest(function (ConsoleOutputBuilder $copy) use ($builder) {
            $this->assertNotSame($builder, $copy);

            $copy->glueWith('-')->add('a')->add('b')->add('c');
        })
            ->nest(function (ConsoleOutputBuilder $copy) {
                $copy->glueWith(' ')->add('d')->add('e')->add('f');
            })
            ->nest(function (ConsoleOutputBuilder $copy) {
                $copy->add('g')->add('h');
            });

        $this->assertSame('a-b-c' . PHP_EOL . 'd e f' . PHP_EOL . 'g' . PHP_EOL . 'h', $builder->toString());
        $this->assertCount(4, $builder->getLines());
        $this->assertSame('a-b-c', $builder->getLines()[0]->line);
        $this->assertSame('d e f', $builder->getLines()[1]->line);
        $this->assertSame('g', $builder->getLines()[2]->line);
        $this->assertSame('h', $builder->getLines()[3]->line);
    }
}
