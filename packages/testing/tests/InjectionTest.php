<?php

namespace Tempest\Testing\Tests;

use Tempest\Testing\Provide;
use Tempest\Testing\Test;
use Tempest\Testing\Tests\Fixtures\Dependency;

use function Tempest\Testing\test;

final readonly class InjectionTest
{
    public function __construct(
        private Dependency $dependency,
    ) {}

    #[Test]
    public function injectInMethod(Dependency $dependency): void
    {
        test($dependency)->instanceOf(Dependency::class);
    }

    #[Test]
    public function injectInConstructor(): void
    {
        test($this->dependency)->instanceOf(Dependency::class);
    }

    #[
        Test,
        Provide(
            ['foo' => 'foo'],
        ),
    ]
    public function combinedWithProvider(Dependency $dependency, string $foo): void
    {
        test($dependency)->instanceOf(Dependency::class);
    }

    #[
        Test,
        Provide(
            ['foo' => 'foo'],
        ),
    ]
    public function combinedWithProviderInReverseOrder(string $foo, Dependency $dependency): void
    {
        test($dependency)->instanceOf(Dependency::class);
    }
}
