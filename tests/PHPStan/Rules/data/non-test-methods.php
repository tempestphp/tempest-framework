<?php

declare(strict_types=1);

namespace Tests\Tempest\PHPStan\Rules\Data\NonTestMethods;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class Helper
{
    public function test_helper_operation(): void {}
}

abstract class ValidTestCase extends TestCase
{
    protected function test_not_public(): void {}

    public function testCamelCaseIsOutsideTheConvention(): void {}

    #[Test]
    public function descriptive_name(): void {}
}
