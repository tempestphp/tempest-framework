<?php

namespace Tempest\Testing\Tests;

use Generator;
use Tempest\Testing\Provide;
use Tempest\Testing\Test;

use function Tempest\Testing\test;

final readonly class ProviderTest
{
    #[
        Test,
        Provide(
            [1, 2],
            [1, 2],
        ),
    ]
    public function provideWithScalarValues(int $one, int $two): void
    {
        test($one)->is(1);
        test($two)->is(2);
    }

    #[
        Test,
        Provide(
            ['two' => 2, 'one' => 1],
            ['two' => 2, 'one' => 1],
        ),
    ]
    public function provideWithNamedScalarValues(int $one, int $two): void
    {
        test($one)->is(1);
        test($two)->is(2);
    }

    #[
        Test,
        Provide(
            'generatorData',
        ),
    ]
    public function provideWithGenerator(int $one, int $two): void
    {
        test($one)->is(1);
        test($two)->is(2);
    }

    #[
        Test,
        Provide(
            'generatorData',
            [1, 2],
        ),
    ]
    public function provideWithGeneratorAndArrays(int $one, int $two): void
    {
        test($one)->is(1);
        test($two)->is(2);
    }

    #[
        Test,
        Provide(static function (): Generator {
            yield [1, 2];
            yield [1, 2];
        }),
    ]
    public function provideWithClosure(int $one, int $two): void
    {
        test($one)->is(1);
        test($two)->is(2);
    }

    public function generatorData(): Generator
    {
        yield [1, 2];
        yield ['two' => 2, 'one' => 1];
    }
}
