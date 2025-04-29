<?php

namespace Tempest\Support\Tests\Math;

use Closure;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Tempest\Support\Math;

use function Tempest\Support\Arr\range;

final class MathsTest extends TestCase
{
    #[TestWith([5, 5])]
    #[TestWith([5, -5])]
    #[TestWith([5.5, -5.5])]
    #[TestWith([10.5, 10.5])]
    public function test_abs(int|float $expected, int|float $number): void
    {
        $this->assertSame($expected, Math\abs($number));
    }

    #[TestWith([0.0, 1.0])]
    #[TestWith([1.2661036727794992, 0.3])]
    #[TestWith([1.0471975511965979, 0.5])]
    public function test_acos(float $expected, float $number): void
    {
        $this->assertFloatEquals($expected, Math\acos($number));
    }

    #[TestWith([0.5235987755982989, 0.5])]
    #[TestWith([0.9272952180016123, 0.8])]
    #[TestWith([0.0, 0.0])]
    #[TestWith([0.41151684606748806, 0.4])]
    public function test_asin(float $expected, float $number): void
    {
        $this->assertFloatEquals($expected, Math\asin($number));
    }

    #[TestWith([0.7853981633974483, 1.0, 1.0])]
    #[TestWith([0.8960553845713439, 1.0, 0.8])]
    #[TestWith([0.0, 0.0, 0.0])]
    #[TestWith([0.7853981633974483, 0.4, 0.4])]
    #[TestWith([-2.260001062633476, -0.5, -0.412])]
    public function test_atan2(float $expected, float $y, float $x): void
    {
        $this->assertFloatEquals($expected, Math\atan2($y, $x));
    }

    #[TestWith([0.7853981633974483, 1.0])]
    #[TestWith([0.6747409422235527, 0.8])]
    #[TestWith([0.0, 0.0])]
    #[TestWith([0.3805063771123649, 0.4])]
    #[TestWith([-0.4636476090008061, -0.5])]
    public function test_atan(float $expected, float $number): void
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
    public function test_base_convert(string $expected, string $value, int $from, int $to): void
    {
        $this->assertSame($expected, Math\base_convert($value, $from, $to));
    }

    #[TestWith([5.0, 5.0])]
    #[TestWith([5.0, 4.8])]
    #[TestWith([0.0, 0.0])]
    #[TestWith([1.0, 0.4])]
    #[TestWith([-6.0, -6.5])]
    public function test_ciel(float $expected, float $number): void
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
    public function test_clamp(int|float $expected, int|float $number, int|float $min, int|float $max): void
    {
        $this->assertSame($expected, Math\clamp($number, $min, $max));
    }

    public function test_clamp_invalid_min_max(): void
    {
        $this->expectException(Math\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected $min to be lower or equal to $max.');

        Math\clamp(10, 20, 10);
    }

    #[TestWith([0.5403023058681398, 1.0])]
    #[TestWith([1.0, 0.0])]
    #[TestWith([0.10291095660695612, 45.45])]
    #[TestWith([0.28366218546322625, -5])]
    #[TestWith([-0.9983206000589924, -15.65])]
    public function test_cos(float $expected, float $number): void
    {
        $this->assertFloatEquals($expected, Math\cos($number));
    }

    #[TestWith([2, 5, 2])]
    #[TestWith([5, 10, 2])]
    #[TestWith([0, 15, 20])]
    #[TestWith([1, 10, 10])]
    public function test_div(int $expected, int $numerator, int $denominator): void
    {
        $this->assertSame($expected, Math\div($numerator, $denominator));
    }

    public function test_div_by_zero(): void
    {
        $this->expectException(Math\Exception\DivisionByZeroException::class);
        $this->expectExceptionMessage('Division by zero.');

        Math\div(10, 0);
    }

    public function test_div_int64_min_by_minus_one(): void
    {
        $this->expectException(Math\Exception\ArithmeticException::class);
        $this->expectExceptionMessage('Division of Math\INT64_MIN by -1 is not an integer.');

        Math\div(Math\INT64_MIN, -1);
    }

    #[TestWith([162754.79141900392, 12.0])]
    #[TestWith([298.8674009670603, 5.7])]
    #[TestWith([Math\INFINITY, 1000000])]
    public function test_exp(float $expected, float $number): void
    {
        $this->assertSame($expected, Math\exp($number));
    }

    #[TestWith([4, 4.3])]
    #[TestWith([9, 9.9])]
    #[TestWith([3, Math\PI])]
    #[TestWith([-4, -Math\PI])]
    #[TestWith([2, Math\E])]
    public function test_floor(float $expected, float $number): void
    {
        $this->assertSame($expected, Math\floor($number));
    }

    #[TestWith([5497, '1010101111001', 2])]
    #[TestWith([2014587925987, 'pphlmw9v', 36])]
    #[TestWith([Math\INT32_MAX, 'zik0zj', 36])]
    #[TestWith([15, 'F', 16])]
    public function test_from_base(int $expected, string $value, int $from_base): void
    {
        $this->assertSame($expected, Math\from_base($value, $from_base));
    }

    public function test_invalid_digit_throws(): void
    {
        $this->expectException(Math\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid digit Z in base 16');

        Math\from_base('Z', 16);
    }

    public function test_special_char_throws(): void
    {
        $this->expectException(Math\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid digit * in base 16');

        Math\from_base('*', 16);
    }

    public function test_throws_for_overflow(): void
    {
        $number = str_repeat('A', times: 100);

        $this->expectException(Math\Exception\OverflowException::class);
        $this->expectExceptionMessage('Unexpected integer overflow parsing ' . $number . ' from base 32');

        Math\from_base($number, 32);
    }

    #[TestWith([1.6863989535702288, 5.4, null])]
    #[TestWith([0.6574784600188808, 5.4, 13])]
    #[TestWith([1.7323937598229686, 54.0, 10])]
    #[TestWith([0, 1, null])]
    public function test_log(float $expected, float $number, ?float $base = null): void
    {
        $this->assertSame($expected, Math\log($number, $base));
    }

    public function test_negative_input_throws(): void
    {
        $this->expectException(Math\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('$number must be positive.');

        Math\log(-45);
    }

    public function test_non_positive_base_throws(): void
    {
        $this->expectException(Math\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('$base must be positive.');

        Math\log(4.4, 0.0);
    }

    public function test_base_one_throws_for_undefined_logarithm(): void
    {
        $this->expectException(Math\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Logarithm undefined for $base of 1.0.');

        Math\log(4.4, 1.0);
    }

    #[DataProvider('provide_max_by_cases')]
    public function test_max_by(string|int|array|null $expected, array $values, Closure $fun): void
    {
        $this->assertSame($expected, Math\max_by($values, $fun));
    }

    public static function provide_max_by_cases(): Generator
    {
        yield [
            'bazqux',
            ['foo', 'bar', 'baz', 'qux', 'foobar', 'bazqux'],
            static fn (string $value): int => mb_strlen($value),
        ];

        yield [
            ['foo', 'bar', 'baz'],
            [
                ['foo'],
                ['foo', 'bar'],
                ['foo', 'bar', 'baz'],
            ],
            static fn (array $arr): int => count($arr),
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
    public function test_max(?int $expected, array $numbers): void
    {
        $this->assertSame($expected, Math\max($numbers));
    }

    #[DataProvider('provide_max_va_cases')]
    public function test_maxva(int $expected, int $first, int $second, int ...$rest): void
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
    public function test_mean(null|int|float $expected, array $numbers): void
    {
        $this->assertSame($expected, Math\mean($numbers));
    }

    public static function provide_mean_cases(): array
    {
        return [
            [5.0, [10, 5, 0, 2, 4, 6, 8]],
            [7.357142857142858, [18, 15, 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 15]],
            [26.785714285714285, [19, 15, 0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 52, 64]],
            [100.0, array_fill(0, 100, 100)],
            [null, []],
        ];
    }

    #[DataProvider('provide_median_cases')]
    public function test_median(?float $expected, array $numbers): void
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
    public function test_min_by(string|int|array|null $expected, array $values, Closure $fun): void
    {
        $this->assertSame($expected, Math\min_by($values, $fun));
    }

    public static function provide_min_by_cases(): Generator
    {
        yield [
            'qux',
            ['foo', 'bar', 'baz', 'qux', 'foobar', 'bazqux'],
            static fn (string $value): int => mb_strlen($value),
        ];

        yield [
            ['foo'],
            [
                ['foo'],
                ['foo', 'bar'],
                ['foo', 'bar', 'baz'],
            ],
            static fn (array $arr): int => count($arr),
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
    public function test_min(?int $expected, array $numbers): void
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
    public function test_min_va(int|float|null $expected, int|float $first, int|float $second, int|float ...$rest): void
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

    #[TestWith([5.46, 5.45663, 2])]
    #[TestWith([4.8, 4.811, 1])]
    #[TestWith([5.0, 5.42, 0])]
    #[TestWith([5.0, 4.8, 0])]
    #[TestWith([0.0, 0.4242, 0])]
    #[TestWith([0.5, 0.4634, 1])]
    #[TestWith([-6.57778, -6.5777777777, 5])]
    public function test_round(float $expected, float $number, int $precision = 0): void
    {
        $this->assertSame($expected, Math\round($number, $precision));
    }

    #[TestWith([-0.9589242746631385, 5.0])]
    #[TestWith([-0.9961646088358407, 4.8])]
    #[TestWith([0.0, 0.0])]
    #[TestWith([0.3894183423086505, 0.4])]
    #[TestWith([-0.21511998808781552, -6.5])]
    public function test_sin(float $expected, float $number): void
    {
        $this->assertFloatEquals($expected, Math\sin($number));
    }

    #[TestWith([2.23606797749979, 5.0])]
    #[TestWith([2.1908902300206643, 4.8])]
    #[TestWith([0.6324555320336759, 0.4])]
    #[TestWith([2.5495097567963922, 6.5])]
    #[TestWith([1.4142135623730951, 2])]
    #[TestWith([1, 1])]
    public function test_sqrt(float $expected, float $number): void
    {
        $this->assertSame($expected, Math\sqrt($number));
    }

    #[DataProvider('provide_sum_floats_data')]
    public function test_sum_floats(float $expected, array $numbers): void
    {
        $this->assertSame($expected, Math\sum_floats($numbers));
    }

    public static function provide_sum_floats_data(): array
    {
        return [
            [116.70000000000005, [10.9, 5, ...range(0, 9.8798, 0.48)]],
            [103.0, [18, 15, ...range(0, 10), 15]],
            [323.54, [19.5, 15.8, ...range(0.5, 45, 5.98), 52.8, 64]],
        ];
    }

    #[DataProvider('provide_sum_data')]
    public function test_sum(int $expected, array $numbers): void
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

    #[TestWith([-3.380515006246586, 5.0, 0.00000000000001])]
    #[TestWith([-11.384870654242922, 4.8])]
    #[TestWith([0.0, 0.0])]
    #[TestWith([0.4227932187381618, 0.4])]
    #[TestWith([-0.22027720034589682, -6.5])]
    public function test_tan(float $expected, float $number, float $epsilon = PHP_FLOAT_EPSILON): void
    {
        $this->assertFloatEquals($expected, Math\tan($number), $epsilon);
    }

    #[TestWith(['1010101111001', 5497, 2])]
    #[TestWith(['pphlmw9v', 2014587925987, 36])]
    #[TestWith(['zik0zj', Math\INT32_MAX, 36])]
    #[TestWith(['f', 15, 16])]
    public function test_to_base(string $expected, int $value, int $to_base): void
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
