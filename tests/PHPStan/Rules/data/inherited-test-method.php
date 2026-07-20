<?php

declare(strict_types=1);

namespace Tests\Tempest\PHPStan\Rules\Data\InheritedTestMethod;

use PHPUnit\Framework\TestCase;

abstract class AbstractTestCase extends TestCase
{
    public function test_inherited_method(): void {}
}

final class ConcreteTest extends AbstractTestCase {}
