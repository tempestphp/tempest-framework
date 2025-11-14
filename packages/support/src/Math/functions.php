<?php

namespace Tempest\Support\Math {
    use ArithmeticError;
    use Closure;
    use DivisionByZeroError;
    use Tempest\Support\Str;

    use function acos as php_acos;
    use function asin as php_asin;
    use function atan as php_atan;
    use function atan2 as php_atan2;
    use function bcadd;
    use function bccomp;
    use function bcdiv;
    use function bcmod;
    use function bcmul;
    use function bcpow;
    use function ceil as php_ceil;
    use function cos as php_cos;
    use function count;
    use function exp as php_exp;
    use function floor as php_floor;
    use function intdiv;
    use function log as php_log;
    use function round as php_round;
    use function sin as php_sin;
    use function sort;
    use function sqrt as php_sqrt;
    use function tan as php_tan;

    /**
     * Returns the square root of the given number.
     *
     * @throws Exception\InvalidArgumentException If $number is negative.
     */
    function sqrt(float $number): float
    {
        if ($number < 0) {
            throw new Exception\InvalidArgumentException('$number must be a non-negative number.');
        }

        return php_sqrt($number);
    }

    /**
     * Returns the absolute value of the given number.
     *
     * @template T of int|float
     *
     * @param T $number
     *
     * @return T
     */
    function abs(int|float $number): int|float
    {
        return $number < 0 ? -$number : $number;
    }

    /**
     * Returns the arc cosine of the given number.
     */
    function acos(float $number): float
    {
        return php_acos($number);
    }

    /**
     * Returns the arc sine of the given number.
     */
    function asin(float $number): float
    {
        return php_asin($number);
    }

    /**
     * Returns the tangent of the given number.
     */
    function tan(float $number): float
    {
        return php_tan($number);
    }

    /**
     * Returns the arc tangent of the given number.
     */
    function atan(float $number): float
    {
        return php_atan($number);
    }

    /**
     * Returns the arc tangent of the given coordinates.
     */
    function atan2(float $y, float $x): float
    {
        return php_atan2($y, $x);
    }

    /**
     * Converts the given string in base `$from_base` to base `$to_base`, assuming
     * letters a-z are used for digits for bases greater than 10. The conversion is
     * done to arbitrary precision.
     *
     * @param non-empty-string $value
     * @param int<2, 36> $fromBase
     * @param int<2, 36> $toBase
     *
     * @throws Exception\InvalidArgumentException If the given value is invalid.
     */
    function base_convert(string $value, int $fromBase, int $toBase): string
    {
        $fromAlphabet = mb_substr(Str\ALPHABET_ALPHANUMERIC, 0, $fromBase);
        $resultDecimal = '0';
        $placeValue = bcpow((string) $fromBase, (string) (strlen($value) - 1));

        foreach (str_split($value) as $digit) {
            $digitNumeric = stripos($fromAlphabet, $digit);

            if (false === $digitNumeric) {
                throw new Exception\InvalidArgumentException(sprintf('Invalid digit %s in base %d', $digit, $fromBase));
            }

            $resultDecimal = bcadd($resultDecimal, bcmul((string) $digitNumeric, $placeValue));
            $placeValue = bcdiv($placeValue, (string) $fromBase);
        }

        if (10 === $toBase) {
            return $resultDecimal;
        }

        $toAlphabet = mb_substr(Str\ALPHABET_ALPHANUMERIC, 0, $toBase);
        $result = '';

        do {
            $result = $toAlphabet[(int) bcmod($resultDecimal, (string) $toBase)] . $result;
            $resultDecimal = bcdiv($resultDecimal, (string) $toBase);
        } while (bccomp($resultDecimal, '0') > 0);

        return $result;
    }

    /**
     * Return the smallest integer value greater than or equal to the given number.
     */
    function ceil(float $number): float
    {
        return php_ceil($number);
    }

    /**
     * Returns the given number clamped to the given range.
     *
     * @template T of float|int
     *
     * @param T $number
     * @param T $min
     * @param T $max
     *
     * @throws Exception\InvalidArgumentException If $min is bigger than $max
     *
     * @return T
     */
    function clamp(int|float $number, int|float $min, int|float $max): int|float
    {
        if ($max < $min) {
            throw new Exception\InvalidArgumentException('Expected $min to be lower or equal to $max.');
        }

        if ($number < $min) {
            return $min;
        }

        if ($number > $max) {
            return $max;
        }

        return $number;
    }

    /**
     * Return the cosine of the given number.
     */
    function cos(float $number): float
    {
        return php_cos($number);
    }

    /**
     * Returns the result of integer division of the given numerator by the given denominator.
     *
     * @throws Exception\ArithmeticException If the $numerator is Math\INT64_MIN and the $denominator is -1.
     * @throws Exception\DivisionByZeroException If the $denominator is 0.
     */
    function div(int $numerator, int $denominator): int
    {
        try {
            return intdiv($numerator, $denominator);
        } catch (DivisionByZeroError $error) {
            throw new Exception\DivisionByZeroException(sprintf('%s.', $error->getMessage()), $error->getCode(), $error);
        } catch (ArithmeticError $error) {
            throw new Exception\ArithmeticException(
                'Division of Math\INT64_MIN by -1 is not an integer.',
                $error->getCode(),
                $error,
            );
        }
    }

    /**
     * Returns the exponential of the given number.
     */
    function exp(float $number): float
    {
        return php_exp($number);
    }

    /**
     * Return the largest integer value less then or equal to the given number.
     */
    function floor(float $number): float
    {
        return php_floor($number);
    }

    /**
     * Converts the given string in base `$from_base` to an integer, assuming letters a-z
     * are used for digits when `$from_base` > 10.
     *
     * @param non-empty-string $number
     * @param int<2, 36> $fromBase
     *
     * @throws Exception\InvalidArgumentException If $number contains an invalid digit in base $from_base
     * @throws Exception\OverflowException In case of an integer overflow
     */
    function from_base(string $number, int $fromBase): int
    {
        $limit = div(INT64_MAX, $fromBase);
        $result = 0;

        foreach (str_split($number) as $digit) {
            $oval = ord($digit);

            // Branches sorted by guesstimated frequency of use. */
            if (/* '0' - '9' */ $oval <= 57 && $oval >= 48) {
                $dval = $oval - 48;
            } elseif (/* 'a' - 'z' */ $oval >= 97 && $oval <= 122) {
                $dval = $oval - 87;
            } elseif (/* 'A' - 'Z' */ $oval >= 65 && $oval <= 90) {
                $dval = $oval - 55;
            } else {
                $dval = 99;
            }

            if ($fromBase < $dval) {
                throw new Exception\InvalidArgumentException(sprintf('Invalid digit %s in base %d', $digit, $fromBase));
            }

            $oldval = $result;
            $result = ($fromBase * $result) + $dval;
            if ($oldval > $limit || $oldval > $result) {
                throw new Exception\OverflowException(sprintf('Unexpected integer overflow parsing %s from base %d', $number, $fromBase));
            }
        }

        return $result;
    }

    /**
     * Converts the given non-negative number into the given base, using letters a-z
     * for digits when then given base is > 10.
     *
     * @param int<0, max> $number
     * @param int<2, 36> $base
     *
     * @return non-empty-string
     */
    function to_base(int $number, int $base): string
    {
        $result = '';

        do {
            $quotient = div($number, $base);
            $result = Str\ALPHABET_ALPHANUMERIC[$number - ($quotient * $base)] . $result;
            $number = $quotient;
        } while (0 !== $number);

        return $result;
    }

    /**
     * Returns the logarithm of the given number.
     *
     * @throws Exception\InvalidArgumentException If $number or $base are negative, or $base is equal to 1.0.
     */
    function log(float $number, ?float $base = null): float
    {
        if ($number <= 0) {
            throw new Exception\InvalidArgumentException('$number must be positive.');
        }

        if (null === $base) {
            return php_log($number);
        }

        if ($base <= 0) {
            throw new Exception\InvalidArgumentException('$base must be positive.');
        }

        if ($base === 1.0) {
            throw new Exception\InvalidArgumentException('Logarithm undefined for $base of 1.0.');
        }

        return php_log($number, $base);
    }

    /**
     * Returns the largest element of the given iterable, or null if the
     * iterable is empty.
     *
     * The value for comparison is determined by the given function.
     *
     * In the case of duplicate values, later values overwrite previous ones.
     *
     * @template T
     *
     * @param iterable<T> $numbers
     * @param (Closure(T): numeric) $numericFunction
     *
     * @return T|null
     */
    function max_by(iterable $numbers, Closure $numericFunction): mixed
    {
        $max = null;
        $maxNum = null;

        foreach ($numbers as $value) {
            $valueNum = $numericFunction($value);
            if (null === $maxNum || $valueNum >= $maxNum) {
                $max = $value;
                $maxNum = $valueNum;
            }
        }

        return $max;
    }

    /**
     * Returns the largest element of the given list, or null if the array is empty.
     *
     * @template T of int|float
     *
     * @param array<T> $numbers
     *
     * @return ($numbers is non-empty-list<T> ? T : null)
     */
    function max(array $numbers): null|int|float
    {
        $max = null;

        foreach ($numbers as $number) {
            if (null === $max || $number > $max) {
                $max = $number;
            }
        }

        return $max;
    }

    /**
     * Returns the largest number of all the given numbers.
     *
     * @template T of int|float
     *
     * @param T $first
     * @param T $second
     * @param T ...$rest
     *
     * @return T
     */
    function maxva(int|float $first, int|float $second, int|float ...$rest): int|float
    {
        $max = \max($first, $second);

        foreach ($rest as $number) {
            if ($number > $max) {
                $max = $number;
            }
        }

        return $max;
    }

    /**
     * Returns the arithmetic mean of the given numbers in the list.
     *
     * Return null if the given list is empty.
     *
     * @param array<int|float> $numbers
     *
     * @return ($numbers is non-empty-list ? float : null)
     */
    function mean(array $numbers): ?float
    {
        $count = (float) count($numbers);

        if (0.0 === $count) {
            return null;
        }

        $mean = 0.0;

        foreach ($numbers as $number) {
            $mean += (float) $number / $count;
        }

        return $mean;
    }

    /**
     * Returns the median of the given numbers in the list.
     *
     * Returns null if the given iterable is empty.
     *
     * @param array<int|float> $numbers
     *
     * @return ($numbers is non-empty-list ? float : null)
     */
    function median(array $numbers): ?float
    {
        sort($numbers);
        $count = count($numbers);

        if (0 === $count) {
            return null;
        }

        $middleIndex = div($count, 2);

        if (0 === ($count % 2)) {
            return mean([$numbers[$middleIndex], $numbers[$middleIndex - 1]]);
        }

        return (float) $numbers[$middleIndex];
    }

    /**
     * Returns the smallest element of the given iterable, or null if the
     * iterable is empty.
     *
     * The value for comparison is determined by the given function.
     *
     * In the case of duplicate values, later values overwrite previous ones.
     *
     * @template T
     *
     * @param iterable<T> $numbers
     * @param (Closure(T): numeric) $numericFunction
     *
     * @return T|null
     */
    function min_by(iterable $numbers, Closure $numericFunction): mixed
    {
        $min = null;
        $minNum = null;

        foreach ($numbers as $value) {
            $valueNum = $numericFunction($value);

            if (null === $minNum || $valueNum <= $minNum) {
                $min = $value;
                $minNum = $valueNum;
            }
        }

        return $min;
    }

    /**
     * Returns the smallest element of the given list, or null if the
     * list is empty.
     *
     * @template T of int|float
     *
     * @param array<T> $numbers
     *
     * @return ($numbers is non-empty-list<T> ? T : null)
     */
    function min(array $numbers): null|float|int
    {
        $min = null;

        foreach ($numbers as $number) {
            if (null === $min || $number < $min) {
                $min = $number;
            }
        }

        return $min;
    }

    /**
     * Returns the smallest number of all the given numbers.
     *
     * @template T of int|float
     *
     * @param T $first
     * @param T $second
     * @param T ...$rest
     *
     * @return T
     */
    function minva(int|float $first, int|float $second, int|float ...$rest): int|float
    {
        $min = \min($first, $second);

        foreach ($rest as $number) {
            if ($number < $min) {
                $min = $number;
            }
        }

        return $min;
    }

    /**
     * Returns the given number rounded to the specified precision.
     *
     * A positive precision rounds to the nearest decimal place whereas a negative precision
     * rounds to the nearest power of ten.
     *
     * For example, a precision of 1 rounds to the nearest tenth whereas a precision of -1 rounds to the nearst nearest.
     */
    function round(float $number, int $precision = 0): float
    {
        return php_round($number, $precision);
    }

    /**
     * Returns the sine of the given number.
     */
    function sin(float $number): float
    {
        return php_sin($number);
    }

    /**
     * Returns the sum of all the given numbers.
     *
     * @param array<int|float> $numbers
     */
    function sum_floats(array $numbers): float
    {
        $result = 0.0;

        foreach ($numbers as $number) {
            $result += (float) $number;
        }

        return $result;
    }

    /**
     * Returns the sum of all the given numbers.
     *
     * @param array<int> $numbers
     */
    function sum(array $numbers): int
    {
        $result = 0;

        foreach ($numbers as $number) {
            $result += $number;
        }

        return $result;
    }
}
