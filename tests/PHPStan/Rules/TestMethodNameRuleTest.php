<?php

declare(strict_types=1);

namespace Tests\Tempest\PHPStan\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * @extends RuleTestCase<TestMethodNameRule>
 */
final class TestMethodNameRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new TestMethodNameRule();
    }

    #[Test]
    public function it_reports_prefixed_test_methods(): void
    {
        $this->analyse(
            [__DIR__ . '/data/PrefixedTestMethods.php'],
            [
                ['Test methods must use the #[Test] attribute instead of the "test_" prefix.', 12],
                ['Test methods must use the #[Test] attribute instead of the "test_" prefix.', 14],
            ],
        );
    }

    #[Test]
    public function it_reports_prefixed_methods_on_indirect_test_case_subclasses(): void
    {
        $this->analyse(
            [__DIR__ . '/data/inherited-test-method.php'],
            [
                ['Test methods must use the #[Test] attribute instead of the "test_" prefix.', 11],
            ],
        );
    }

    #[Test]
    public function it_ignores_methods_that_phpunit_would_not_discover_by_prefix(): void
    {
        $this->analyse([__DIR__ . '/data/non-test-methods.php'], []);
    }
}
