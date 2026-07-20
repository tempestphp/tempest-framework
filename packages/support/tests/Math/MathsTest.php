<?php

namespace Tempest\Support\Tests\Math;

use Closure;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Tempest\Support\Math;
use Tempest\Support\Math\Exception\ArithmeticException;
use Tempest\Support\Math\Exception\DivisionByZeroException;
use Tempest\Support\Math\Exception\InvalidArgumentException;
use Tempest\Support\Math\Exception\OverflowException;

use function Tempest\Support\Arr\range;

final class MathsTest extends TestCase
{
    #[TestWith([5, 5])]
    #[TestWith([5, -5])]
    #[TestWith([5.5, -5.5])]
    #[TestWith([10.5, 10.5])]
    #[Test]
    public function abs(int|float $expected, int|float $number): void
    {
        $this->assertSame($expected, Math\abs($number));
    }

    #[TestWith([0.0, 1.0])]
    #[TestWith([1.266_103_672_779_499_2, 0.3])]
    #[TestWith([1.047_197_551_196_597_9, 0.5])]
    #[Test]
    public function acos(float $expected, float $number): void
    {
        $this->assertFloatEquals($expected, Math\acos($number));
    }

    #[TestWith([0.523_598_775_598_298_9, 0.5])]
    #[TestWith([0.927_295_218_001_612_3, 0.8])]
    #[TestWith([0.0, 0.0])]
    #[TestWith([0.411_516_846_067_488_06, 0.4])]
    #[Test]
    public function asin(float $expected, float $number): void
    {
        $this->assertFloatEquals($expected, Math\asin($number));
    }

    #[TestWith([0.785_398_163_397_448_3, 1.0, 1.0])]
    #[TestWith([0.896_055_384_571_343_9, 1.0, 0.8])]
    #[TestWith([0.0, 0.0, 0.0])]
    #[TestWith([0.785_398_163_397_448_3, 0.4, 0.4])]
    #[TestWith([-2.260_001_062_633_476, -0.5, -0.412])]
    #[Test]
    public function atan2(float $expected, float $y, float $x): void
    {
        $this->assertFloatEquals($expected, Math\atan2($y, $x));
    }

    #[TestWith([0.785_398_163_397_448_3, 1.0])]
    #[TestWith([0.674_740_942_223_552_7, 0.8])]
    #[TestWith([0.0, 0.0])]
    #[TestWith([0.380_506_377_112_364_9, 0.4])]
    #[TestWith([-0.463_647_609_000_806_1, -0.5])]
    #[Test]
    public function atan(float $expected, float $number): void
    {
        $this->assertFloatEquals($expected, Math\atan($number));
    }

    #[TestWith(['2', '10', 2, 16])]
    #[TestWith(['2', '10', 2, 10])]
    #[TestWith(['f', '15', 10, 16])]
    #[TestWith(['10', '2', 16, 2])]
    #[TestWith(['1010101111001', '5497', 10, 2])]
    #[TestWith(['48p', '1010101111001', 2, 36])]
    #[TestWith(['pphlmw9v', '2014587925987', 10, 36])]
    #[TestWith(['zik0zj', '2147483647', 10, 36])]
    #[Test]
    public function base_convert(string $expected, string $value, int $from, int $to): void
    {
        $this->assertSame($expected, Math\base_convert($value, $from, $to));
    }

    #[TestWith([5.0, 5.0])]
    #[TestWith([5.0, 4.8])]
    #[TestWith([0.0, 0.0])]
    #[TestWith([1.0, 0.4])]
    #[TestWith([-6.0, -6.5])]
    #[Test]
    public function ciel(float $expected, float $number): void
    {
        $this->assertSame($expected, Math\ceil($number));
    }

    #[TestWith([10, 10, 2, 20])]
    #[TestWith([10, 20, 1, 10])]
    #[TestWith([10, 5, 10, 20])]
    #[TestWith([10, 10, 10, 20])]
    #[TestWith([10, 10, 1, 10])]
    #[TestWith([10, 20, 10, 10])]
    #[TestWith([10.0, 10.0, 2.0, 20.0])]
    #[Test]
    public function clamp(int|float $expected, int|float $number, int|float $min, int|float $max): void
    {
        $this->assertSame($expected, Math\clamp($number, $min, $max));
    }

    #[Test]
    public function clamp_invalid_min_max(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected $min to be lower or equal to $max.');

        Math\clamp(10, 20, 10);
    }

    #[TestWith([0.540_302_305_868_139_8, 1.0])]
    #[TestWith([1.0, 0.0])]
    #[TestWith([0.102_910_956_606_956_12, 45.45])]
    #[TestWith([0.283_662_185_463_226_25, -5])]
    #[TestWith([-0.998_320_600_058_992_4, -15.65])]
    #[Test]
    public function cos(float $expected, float $number): void
    {
        $this->assertFloatEquals($expected, Math\cos($number));
    }

    #[TestWith([2, 5, 2])]
    #[TestWith([5, 10, 2])]
    #[TestWith([0, 15, 20])]
    #[TestWith([1, 10, 10])]
    #[Test]
    public function div(int $expected, int $numerator, int $denominator): void
    {
        $this->assertSame($expected, Math\div($numerator, $denominator));
    }

    #[Test]
    public function div_by_zero(): void
    {
        $this->expectException(DivisionByZeroException::class);
        $this->expectExceptionMessage('Division by zero.');

        Math\div(10, 0);
    }

    #[Test]
    public function div_int64_min_by_minus_one(): void
    {
        $this->expectException(ArithmeticException::class);
        $this->expectExceptionMessage('Division of Math\INT64_MIN by -1 is not an integer.');

        Math\div(Math\INT64_MIN, -1);
    }

    #[TestWith([162_754.791_419_003_92, 12.0])]
    #[TestWith([298.867_400_967_060_3, 5.7])]
    #[TestWith([Math\INFINITY, 1_000_000])]
    #[Test]
    public function exp(float $expected, float $number): void
    {
        $this->assertSame($expected, Math\exp($number));
    }

    #[TestWith([4, 4.3])]
    #[TestWith([9, 9.9])]
    #[TestWith([3, Math\PI])]
    #[TestWith([-4, -Math\PI])]
    #[TestWith([2, Math\E])]
    #[Test]
    public function floor(float $expected, float $number): void
    {
        $this->assertSame($expected, Math\floor($number));
    }

    #[TestWith([5497, '1010101111001', 2])]
    #[TestWith([2_014_587_925_987, 'pphlmw9v', 36])]
    #[TestWith([Math\INT32_MAX, 'zik0zj', 36])]
    #[TestWith([15, 'F', 16])]
    #[Test]
    public function from_base(int $expected, string $value, int $from_base): void
    {
        $this->assertSame($expected, Math\from_base($value, $from_base));
    }

    #[Test]
    public function invalid_digit_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid digit Z in base 16');

        Math\from_base('Z', 16);
    }

    #[Test]
    public function special_char_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid digit * in base 16');

        Math\from_base('*', 16);
    }

    #[Test]
    public function throws_for_overflow(): void
    {
        $number = str_repeat('A', times: 100);

        $this->expectException(OverflowException::class);
        $this->expectExceptionMessage('Unexpected integer overflow parsing ' . $number . ' from base 32');

        Math\from_base($number, 32);
    }

    #[TestWith([1.686_398_953_570_228_8, 5.4, null])]
    #[TestWith([0.657_478_460_018_880_8, 5.4, 13])]
    #[TestWith([1.732_393_759_822_968_6, 54.0, 10])]
    #[TestWith([0, 1, null])]
    #[Test]
    public function log(float $expected, float $number, ?float $base = null): void
    {
        $this->assertSame($expected, Math\log($number, $base));
    }

    #[Test]
    public function negative_input_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$number must be positive.');

        Math\log(-45);
    }

    #[Test]
    public function non_positive_base_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$base must be positive.');

        Math\log(4.4, 0.0);
    }

    #[Test]
    public function base_one_throws_for_undefined_logarithm(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Logarithm undefined for $base of 1.0.');

        Math\log(4.4, 1.0);
    }

    #[DataProvider('provide_max_by_cases')]
    #[Test]
    public function max_by(string|int|array|null $expected, array $values, Closure $fun): void
    {
        $this->assertSame($expected, Math\max_by($values, $fun));
    }

    public static function provide_max_by_cases(): Generator
    {
        yield [
            'bazqux',
            ['foo', 'bar', 'baz', 'qux', 'foobar', 'bazqux'],
            mb_strlen(...),
        ];

        yield [
            ['foo', 'bar', 'baz'],
            [
                ['foo'],
                ['foo', 'bar'],
                ['foo', 'bar', 'baz'],
            ],
            count(...),
        ];

        yield [
            9,
            [0, 1, 2, 3, 4, 5, 6, 7, 8, 9],
            static fn (int $i): int => $i,
        ];

        yield [
            null,
            [],
            static fn (int $i): int => $i,
        ];
    }

    #[TestWith([10, [0, 2, 4, 6, 8, 10]])]
    #[TestWith([15, [0, 2, 4, 6, 8, 10, 15]])]
    #[TestWith([null, []])]
    #[Test]
    public function max(?int $expected, array $numbers): void
    {
        $this->assertSame($expected, Math\max($numbers));
    }

    #[DataProvider('provide_max_va_cases')]
    #[Test]
    public function maxva(int $expected, int $first, int $second, int ...$rest): void
    {
        $this->assertSame($expected, Math\maxva($first, $second, ...$rest));
    }

    public static function provide_max_va_cases(): array
    {
        return [
            [10, 10, 5, ...range(0, 9, 2)],
            [18, 18, 15, ...range(0, 10), 15],
            [64, 19, 15, ...range(0, 45, 5), 52, 64],
        ];
    }

    #[DataProvider('provide_mean_cases')]
    #[Test]
    public function mean(int|float|null $expected, array $numbers): void
    {
        $this->assertSame($expected, Math\mean($numbers));
    }

    public static function provide_mean_cases(): array
    {
        return [
            [5.0, [10, 5, 0, 2, 4, 6, 8]],
            [7.357_142_857_142_858, [18, 15, 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 15]],
            [26.785_714_285_714_285, [19, 15, 0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 52, 64]],
            [100.0, array_fill(0, 100, 100)],
            [null, []],
        ];
    }

    #[DataProvider('provide_median_cases')]
    #[Test]
    public function median(?float $expected, array $numbers): void
    {
        $this->assertSame($expected, Math\median($numbers));
    }

    public static function provide_median_cases(): array
    {
        return [
            [5.0, [10, 5, ...range(0, 9, 2)]],
            [6.5, [18, 15, ...range(0, 10), 15]],
            [22.5, [19, 15, ...range(0, 45, 5), 52, 64]],
            [100.0, array_fill(0, 100, 100)],
            [null, []],
        ];
    }

    #[DataProvider('provide_min_by_cases')]
    #[Test]
    public function min_by(string|int|array|null $expected, array $values, Closure $fun): void
    {
        $this->assertSame($expected, Math\min_by($values, $fun));
    }

    public static function provide_min_by_cases(): Generator
    {
        yield [
            'qux',
            ['foo', 'bar', 'baz', 'qux', 'foobar', 'bazqux'],
            mb_strlen(...),
        ];

        yield [
            ['foo'],
            [
                ['foo'],
                ['foo', 'bar'],
                ['foo', 'bar', 'baz'],
            ],
            count(...),
        ];

        yield [
            0,
            [...range(0, 9)],
            static fn (int $i): int => $i,
        ];

        yield [
            null,
            [],
            static fn (int $i): int => $i,
        ];
    }

    #[DataProvider('provide_min_cases')]
    #[Test]
    public function min(?int $expected, array $numbers): void
    {
        $this->assertSame($expected, Math\min($numbers));
    }

    public static function provide_min_cases(): array
    {
        return [
            [0, [...range(0, 10, 2)]],
            [4, [...range(5, 10), 4]],
            [null, []],
        ];
    }

    #[DataProvider('provide_min_va_cases')]
    #[Test]
    public function min_va(int|float|null $expected, int|float $first, int|float $second, int|float ...$rest): void
    {
        $this->assertSame($expected, Math\minva($first, $second, ...$rest));
    }

    public static function provide_min_va_cases(): array
    {
        return [
            [5, 10, 5, ...range(7, 9, 2)],
            [4, 18, 15, ...range(4, 10), 15],
            [15, 19, 15, ...range(40, 45, 5), 52, 64],
        ];
    }

    #[TestWith([5.46, 5.456_63, 2])]
    #[TestWith([4.8, 4.811, 1])]
    #[TestWith([5.0, 5.42, 0])]
    #[TestWith([5.0, 4.8, 0])]
    #[TestWith([0.0, 0.4242, 0])]
    #[TestWith([0.5, 0.4634, 1])]
    #[TestWith([-6.577_78, -6.577_777_777_7, 5])]
    #[Test]
    public function round(float $expected, float $number, int $precision = 0): void
    {
        $this->assertSame($expected, Math\round($number, $precision));
    }

    #[TestWith([-0.958_924_274_663_138_5, 5.0])]
    #[TestWith([-0.996_164_608_835_840_7, 4.8])]
    #[TestWith([0.0, 0.0])]
    #[TestWith([0.389_418_342_308_650_5, 0.4])]
    #[TestWith([-0.215_119_988_087_815_52, -6.5])]
    #[Test]
    public function sin(float $expected, float $number): void
    {
        $this->assertFloatEquals($expected, Math\sin($number));
    }

    #[TestWith([2.236_067_977_499_79, 5.0])]
    #[TestWith([2.190_890_230_020_664_3, 4.8])]
    #[TestWith([0.632_455_532_033_675_9, 0.4])]
    #[TestWith([2.549_509_756_796_392_2, 6.5])]
    #[TestWith([1.414_213_562_373_095_1, 2])]
    #[TestWith([1, 1])]
    #[Test]
    public function sqrt(float $expected, float $number): void
    {
        $this->assertSame($expected, Math\sqrt($number));
    }

    #[DataProvider('provide_sum_floats_data')]
    #[Test]
    public function sum_floats(float $expected, array $numbers): void
    {
        $this->assertSame($expected, Math\sum_floats($numbers));
    }

    public static function provide_sum_floats_data(): array
    {
        return [
            [116.700_000_000_000_05, [10.9, 5, ...range(0, 9.8798, 0.48)]],
            [103.0, [18, 15, ...range(0, 10), 15]],
            [323.54, [19.5, 15.8, ...range(0.5, 45, 5.98), 52.8, 64]],
        ];
    }

    #[DataProvider('provide_sum_data')]
    #[Test]
    public function sum(int $expected, array $numbers): void
    {
        $this->assertSame($expected, Math\sum($numbers));
    }

    public static function provide_sum_data(): array
    {
        return [
            [60, [10, 5, ...range(0, 9)]],
            [103, [18, 15, ...range(0, 10), 15]],
            [534, [178, 15, ...range(0, 45, 5), 52, 64]],
        ];
    }

    #[TestWith([-3.380_515_006_246_586, 5.0, 0.000_000_000_000_01])]
    #[TestWith([-11.384_870_654_242_922, 4.8])]
    #[TestWith([0.0, 0.0])]
    #[TestWith([0.422_793_218_738_161_8, 0.4])]
    #[TestWith([-0.220_277_200_345_896_82, -6.5])]
    #[Test]
    public function tan(float $expected, float $number, float $epsilon = PHP_FLOAT_EPSILON): void
    {
        $this->assertFloatEquals($expected, Math\tan($number), $epsilon);
    }

    #[TestWith(['1010101111001', 5497, 2])]
    #[TestWith(['pphlmw9v', 2_014_587_925_987, 36])]
    #[TestWith(['zik0zj', Math\INT32_MAX, 36])]
    #[TestWith(['f', 15, 16])]
    #[Test]
    public function to_base(string $expected, int $value, int $to_base): void
    {
        $this->assertSame($expected, Math\to_base($value, $to_base));
    }

    /**
     * Because not all systems have the same rounding rules and precisions,
     * This method provides an assertion to compare floats based on epsilon.
     *
     * @see https://www.php.net/manual/en/language.types.float.php#language.types.float.comparison
     */
    public function assertFloatEquals(float $a, float $b, float $epsilon = PHP_FLOAT_EPSILON): void
    {
        $this->assertTrue(
            Math\abs($a - $b) <= $epsilon,
            'Failed asserting that float ' . $a . ' is equal to ' . $b . '.',
        );
    }
}
