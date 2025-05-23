<?php

declare(strict_types=1);

namespace Tempest\Reflection\Tests\Fixtures;

final class TestClassA
{
    public function method(TestEnum $enum, TestBackedEnum $backedEnum, string $other)
    {
    }
}
