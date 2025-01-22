<?php

declare(strict_types=1);

namespace Tempest\Support\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Support\ArrayHelper;
use Tempest\Support\Tests\Fixtures\Enums\EmptyEnum;
use Tempest\Support\Tests\Fixtures\Enums\SampleStatusBackedEnum;
use Tempest\Support\Tests\Fixtures\Enums\SampleStatusPureEnum;

/**
 * @internal
 */
final class HelperMethodsTest extends TestCase
{
    #[Test]
    public function names_method_backed_enum(): void
    {
        $this->assertSame(
            expected: SampleStatusBackedEnum::names(),
            actual: [
                'PUBLISH',
                'DRAFT',
                'TRASH',
            ],
        );
    }

    #[Test]
    public function names_method_pure_enum(): void
    {
        $this->assertSame(
            expected: SampleStatusPureEnum::names(),
            actual: [
                'PUBLISH',
                'DRAFT',
                'TRASH',
            ],
        );
    }

    #[Test]
    public function names_returns_empty_array_with_empty_enum(): void
    {
        $this->assertSame(
            expected: EmptyEnum::names(),
            actual: [],
        );
    }

    #[Test]
    public function values_method_backed_enum(): void
    {
        $this->assertSame(
            expected: SampleStatusBackedEnum::values(),
            actual: [
                'publish',
                'draft',
                'trash',
            ],
        );
    }

    #[Test]
    public function values_method_pure_enum(): void
    {
        $this->assertSame(
            expected: SampleStatusPureEnum::values(),
            actual: [
                'PUBLISH',
                'DRAFT',
                'TRASH',
            ],
        );
    }

    #[Test]
    public function values_returns_empty_array_with_empty_enum(): void
    {
        $this->assertSame(
            expected: EmptyEnum::values(),
            actual: [],
        );
    }

    #[Test]
    public function collect(): void
    {
        $this->assertSame(
            expected: SampleStatusBackedEnum::cases(),
            actual: SampleStatusBackedEnum::collect()->toArray(),
        );

        $this->assertInstanceOf(
            expected: ArrayHelper::class,
            actual: SampleStatusBackedEnum::collect(),
        );
    }

    #[Test]
    public function options_method_backed_enum(): void
    {
        $this->assertSame(
            expected: SampleStatusBackedEnum::options(),
            actual: [
                'PUBLISH' => 'publish',
                'DRAFT' => 'draft',
                'TRASH' => 'trash',
            ],
        );
    }

    #[Test]
    public function options_method_pure_enum(): void
    {
        $this->assertSame(
            expected: SampleStatusPureEnum::options(),
            actual: [
                'PUBLISH',
                'DRAFT',
                'TRASH',
            ],
        );
    }

    #[Test]
    public function options_returns_empty_array_with_empty_enum(): void
    {
        $this->assertSame(
            expected: EmptyEnum::options(),
            actual: [],
        );
    }
}
