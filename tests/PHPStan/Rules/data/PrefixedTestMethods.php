<?php

declare(strict_types=1);

namespace Tests\Tempest\PHPStan\Rules\Data\PrefixedTestMethods;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PrefixedTestMethods extends TestCase
{
    public function test_without_attribute(): void {}

    #[Test]
    public function test_with_attribute(): void {}

    #[Test]
    public function descriptive_name(): void {}

    public function testCamelCaseIsOutsideTheConvention(): void {}
}
