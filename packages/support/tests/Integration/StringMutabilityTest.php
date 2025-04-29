<?php

declare(strict_types=1);

namespace Tempest\Support\Tests\Integration;

use PHPUnit\Framework\Attributes\TestWith;
use Tempest\Support\Html\HtmlString;
use Tempest\Support\Str\ImmutableString;
use Tempest\Support\Str\MutableString;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class StringMutabilityTest extends FrameworkIntegrationTestCase
{
    use GeneratesArguments;

    /**
     * These methods are either irrelevant to test, or their arguments make them difficult to generate values for.
     */
    private const array EXCLUDED_METHODS = [
        'dd',
        'dump',
        'when',
        'unless',
        'tap',
    ];

    public function test_immutable_string(): void
    {
        $this->assertAllMethods(
            ImmutableString::class,
            function (object $expected, object $actual, string $method): void {
                $this->assertNotSame($expected, $actual, "Method `{$method}` is not immutable.");
                $this->assertEquals('tempest', $expected->toString());
            },
            except: self::EXCLUDED_METHODS,
        );
    }

    public function test_mutable_string(): void
    {
        $this->assertAllMethods(
            MutableString::class,
            function (object $expected, object $actual, string $method): void {
                $this->assertSame($expected, $actual, "Method `{$method}` is not mutable.");
            },
            except: self::EXCLUDED_METHODS,
        );
    }

    #[TestWith([MutableString::class, ImmutableString::class, 'toImmutableString'])]
    #[TestWith([MutableString::class, HtmlString::class, 'toHtmlString'])]
    #[TestWith([ImmutableString::class, MutableString::class, 'toMutableString'])]
    #[TestWith([ImmutableString::class, HtmlString::class, 'toHtmlString'])]
    #[TestWith([HtmlString::class, MutableString::class, 'toMutableString'])]
    #[TestWith([HtmlString::class, ImmutableString::class, 'toImmutableString'])]
    public function test_convert_between_string_instances(string $initial, string $target, string $method): void
    {
        $instance = new $initial('foo');

        $this->assertInstanceOf($target, $instance->$method());
        $this->assertSame('foo', $instance->value);
    }
}
