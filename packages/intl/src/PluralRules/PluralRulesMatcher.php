<?php

namespace Tempest\Intl\PluralRules;

use Tempest\Intl\Locale;

/**
 * This file was auto-generated using the plural rules CLDR dataset.
 * Generated on: 2025-06-21 14:21:38
 */
final class PluralRulesMatcher
{
    /**
     * Extracts the integer part of a number.
     */
    private static function getIntegerPart(float|int $n): int
    {
        return (int) abs($n);
    }

    /**
     * Counts visible fractional digits.
     */
    private static function getVisibleFractionalDigits(float|int $n): int
    {
        $str = (string) $n;

        if (! str_contains($str, '.')) {
            return 0;
        }

        return strlen(rtrim(explode('.', $str)[1], '0'));
    }

    /**
     * Gets fractional digits as integer.
     */
    private static function getFractionalDigits(float|int $n): int
    {
        $str = (string) $n;

        if (! str_contains($str, '.')) {
            return 0;
        }

        return ((int) rtrim(explode('.', $str)[1], '0')) ?: 0;
    }

    /**
     * Gets compact decimal exponent (magnitude).
     */
    private static function getCompactExponent(float|int $n): int
    {
        if ($n === 0 || $n === 0.0) {
            return 0;
        }

        $abs = abs($n);

        if ($abs >= 1000000) {
            return 6;
        }

        if ($abs >= 1000) {
            return 3;
        }

        return 0;
    }

    /**
     * Gets the exponent for scientific notation.
     */
    private static function getExponent(float|int $n): int
    {
        if ($n === 0 || $n === 0.0) {
            return 0;
        }

        return (int) floor(log10(abs($n)));
    }

    /**
     * Checks if number is in range.
     */
    private static function inRange(int|float $value, int|float $start, int|float $end): bool
    {
        return $value >= $start && $value <= $end;
    }

    /**
     * Checks if number matches any value in comma-separated list.
     */
    private static function matchesValues(int|float $value, string $values): bool
    {
        $parts = explode(',', $values);

        foreach ($parts as $part) {
            $part = trim($part);

            if (str_contains($part, '~')) {
                [$start, $end] = explode('~', $part);

                if (self::inRange($value, (float) trim($start), (float) trim($end))) {
                    return true;
                }
            } elseif (str_contains($part, '..')) {
                [$start, $end] = explode('..', $part);

                if (self::inRange($value, (float) trim($start), (float) trim($end))) {
                    return true;
                }
            } elseif (((float) $part) === ((float) $value)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Gets the plural category for the af locale.
     */
    private static function getPluralCategoryAf(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the ak locale.
     */
    private static function getPluralCategoryAk(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if (self::inRange($n, 0, 1)) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the am locale.
     */
    private static function getPluralCategoryAm(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($i === 0 || $n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the an locale.
     */
    private static function getPluralCategoryAn(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the ar locale.
     */
    private static function getPluralCategoryAr(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 0) {
            return 'zero';
        }

        if ($n === 1) {
            return 'one';
        }

        if ($n === 2) {
            return 'two';
        }

        if (self::inRange($n % 100, 3, 10)) {
            return 'few';
        }

        if (self::inRange($n % 100, 11, 99)) {
            return 'many';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the ars locale.
     */
    private static function getPluralCategoryArs(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 0) {
            return 'zero';
        }

        if ($n === 1) {
            return 'one';
        }

        if ($n === 2) {
            return 'two';
        }

        if (self::inRange($n % 100, 3, 10)) {
            return 'few';
        }

        if (self::inRange($n % 100, 11, 99)) {
            return 'many';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the as locale.
     */
    private static function getPluralCategoryAs(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($i === 0 || $n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the asa locale.
     */
    private static function getPluralCategoryAsa(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the ast locale.
     */
    private static function getPluralCategoryAst(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($i === 1 && $v === 0) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the az locale.
     */
    private static function getPluralCategoryAz(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the bal locale.
     */
    private static function getPluralCategoryBal(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the be locale.
     */
    private static function getPluralCategoryBe(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if (($n % 10) === 1 && ($n % 100) !== 11) {
            return 'one';
        }

        if (self::inRange($n % 10, 2, 4) && ! self::inRange($n % 100, 12, 14)) {
            return 'few';
        }

        if (($n % 10) === 0 || self::inRange($n % 10, 5, 9) || self::inRange($n % 100, 11, 14)) {
            return 'many';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the bem locale.
     */
    private static function getPluralCategoryBem(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the bez locale.
     */
    private static function getPluralCategoryBez(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the bg locale.
     */
    private static function getPluralCategoryBg(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the bho locale.
     */
    private static function getPluralCategoryBho(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if (self::inRange($n, 0, 1)) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the blo locale.
     */
    private static function getPluralCategoryBlo(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 0) {
            return 'zero';
        }

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the bm locale.
     */
    private static function getPluralCategoryBm(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        return 'other';
    }

    /**
     * Gets the plural category for the bn locale.
     */
    private static function getPluralCategoryBn(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($i === 0 || $n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the bo locale.
     */
    private static function getPluralCategoryBo(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        return 'other';
    }

    /**
     * Gets the plural category for the br locale.
     */
    private static function getPluralCategoryBr(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if (($n % 10) === 1 && ! (($n % 100) === 11 || ($n % 100) === 71 || ($n % 100) === 91)) {
            return 'one';
        }

        if (($n % 10) === 2 && ! (($n % 100) === 12 || ($n % 100) === 72 || ($n % 100) === 92)) {
            return 'two';
        }

        if ((self::inRange($n % 10, 3, 4) || ($n % 10) === 9) && ! (self::inRange($n % 100, 10, 19) || self::inRange($n % 100, 70, 79) || self::inRange($n % 100, 90, 99))) {
            return 'few';
        }

        if ($n !== 0 && ($n % 1000000) === 0) {
            return 'many';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the brx locale.
     */
    private static function getPluralCategoryBrx(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the bs locale.
     */
    private static function getPluralCategoryBs(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($v === 0 && ($i % 10) === 1 && ($i % 100) !== 11 || ($f % 10) === 1 && ($f % 100) !== 11) {
            return 'one';
        }

        if ($v === 0 && self::inRange($i % 10, 2, 4) && ! self::inRange($i % 100, 12, 14) || self::inRange($f % 10, 2, 4) && ! self::inRange($f % 100, 12, 14)) {
            return 'few';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the ca locale.
     */
    private static function getPluralCategoryCa(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($i === 1 && $v === 0) {
            return 'one';
        }

        if ($e === 0 && $i !== 0 && ($i % 1000000) === 0 && $v === 0 || ! self::inRange($e, 0, 5)) {
            return 'many';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the ce locale.
     */
    private static function getPluralCategoryCe(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the ceb locale.
     */
    private static function getPluralCategoryCeb(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if (
            $v === 0 && ($i === 1 || $i === 2 || $i === 3) ||
                $v === 0 && ! (($i % 10) === 4 || ($i % 10) === 6 || ($i % 10) === 9) ||
                $v !== 0 && ! (($f % 10) === 4 || ($f % 10) === 6 || ($f % 10) === 9)
        ) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the cgg locale.
     */
    private static function getPluralCategoryCgg(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the chr locale.
     */
    private static function getPluralCategoryChr(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the ckb locale.
     */
    private static function getPluralCategoryCkb(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the cs locale.
     */
    private static function getPluralCategoryCs(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($i === 1 && $v === 0) {
            return 'one';
        }

        if (self::inRange($i, 2, 4) && $v === 0) {
            return 'few';
        }

        if ($v !== 0) {
            return 'many';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the csw locale.
     */
    private static function getPluralCategoryCsw(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if (self::inRange($n, 0, 1)) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the cy locale.
     */
    private static function getPluralCategoryCy(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 0) {
            return 'zero';
        }

        if ($n === 1) {
            return 'one';
        }

        if ($n === 2) {
            return 'two';
        }

        if ($n === 3) {
            return 'few';
        }

        if ($n === 6) {
            return 'many';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the da locale.
     */
    private static function getPluralCategoryDa(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1 || $t !== 0 && ($i === 0 || $i === 1)) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the de locale.
     */
    private static function getPluralCategoryDe(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($i === 1 && $v === 0) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the doi locale.
     */
    private static function getPluralCategoryDoi(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($i === 0 || $n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the dsb locale.
     */
    private static function getPluralCategoryDsb(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($v === 0 && ($i % 100) === 1 || ($f % 100) === 1) {
            return 'one';
        }

        if ($v === 0 && ($i % 100) === 2 || ($f % 100) === 2) {
            return 'two';
        }

        if ($v === 0 && self::inRange($i % 100, 3, 4) || self::inRange($f % 100, 3, 4)) {
            return 'few';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the dv locale.
     */
    private static function getPluralCategoryDv(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the dz locale.
     */
    private static function getPluralCategoryDz(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        return 'other';
    }

    /**
     * Gets the plural category for the ee locale.
     */
    private static function getPluralCategoryEe(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the el locale.
     */
    private static function getPluralCategoryEl(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the en locale.
     */
    private static function getPluralCategoryEn(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($i === 1 && $v === 0) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the eo locale.
     */
    private static function getPluralCategoryEo(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the es locale.
     */
    private static function getPluralCategoryEs(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        if ($e === 0 && $i !== 0 && ($i % 1000000) === 0 && $v === 0 || ! self::inRange($e, 0, 5)) {
            return 'many';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the et locale.
     */
    private static function getPluralCategoryEt(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($i === 1 && $v === 0) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the eu locale.
     */
    private static function getPluralCategoryEu(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the fa locale.
     */
    private static function getPluralCategoryFa(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($i === 0 || $n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the ff locale.
     */
    private static function getPluralCategoryFf(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($i === 0 || $i === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the fi locale.
     */
    private static function getPluralCategoryFi(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($i === 1 && $v === 0) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the fil locale.
     */
    private static function getPluralCategoryFil(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if (
            $v === 0 && ($i === 1 || $i === 2 || $i === 3) ||
                $v === 0 && ! (($i % 10) === 4 || ($i % 10) === 6 || ($i % 10) === 9) ||
                $v !== 0 && ! (($f % 10) === 4 || ($f % 10) === 6 || ($f % 10) === 9)
        ) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the fo locale.
     */
    private static function getPluralCategoryFo(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the fr locale.
     */
    private static function getPluralCategoryFr(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($i === 0 || $i === 1) {
            return 'one';
        }

        if ($e === 0 && $i !== 0 && ($i % 1000000) === 0 && $v === 0 || ! self::inRange($e, 0, 5)) {
            return 'many';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the fur locale.
     */
    private static function getPluralCategoryFur(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the fy locale.
     */
    private static function getPluralCategoryFy(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($i === 1 && $v === 0) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the ga locale.
     */
    private static function getPluralCategoryGa(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        if ($n === 2) {
            return 'two';
        }

        if (self::inRange($n, 3, 6)) {
            return 'few';
        }

        if (self::inRange($n, 7, 10)) {
            return 'many';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the gd locale.
     */
    private static function getPluralCategoryGd(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1 || $n === 11) {
            return 'one';
        }

        if ($n === 2 || $n === 12) {
            return 'two';
        }

        if (self::inRange($n, 3, 10) || self::inRange($n, 13, 19)) {
            return 'few';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the gl locale.
     */
    private static function getPluralCategoryGl(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($i === 1 && $v === 0) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the gsw locale.
     */
    private static function getPluralCategoryGsw(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the gu locale.
     */
    private static function getPluralCategoryGu(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($i === 0 || $n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the guw locale.
     */
    private static function getPluralCategoryGuw(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if (self::inRange($n, 0, 1)) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the gv locale.
     */
    private static function getPluralCategoryGv(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($v === 0 && ($i % 10) === 1) {
            return 'one';
        }

        if ($v === 0 && ($i % 10) === 2) {
            return 'two';
        }

        if ($v === 0 && (($i % 100) === 0 || ($i % 100) === 20 || ($i % 100) === 40 || ($i % 100) === 60 || ($i % 100) === 80)) {
            return 'few';
        }

        if ($v !== 0) {
            return 'many';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the ha locale.
     */
    private static function getPluralCategoryHa(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the haw locale.
     */
    private static function getPluralCategoryHaw(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the he locale.
     */
    private static function getPluralCategoryHe(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($i === 1 && $v === 0 || $i === 0 && $v !== 0) {
            return 'one';
        }

        if ($i === 2 && $v === 0) {
            return 'two';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the hi locale.
     */
    private static function getPluralCategoryHi(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($i === 0 || $n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the hnj locale.
     */
    private static function getPluralCategoryHnj(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        return 'other';
    }

    /**
     * Gets the plural category for the hr locale.
     */
    private static function getPluralCategoryHr(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($v === 0 && ($i % 10) === 1 && ($i % 100) !== 11 || ($f % 10) === 1 && ($f % 100) !== 11) {
            return 'one';
        }

        if ($v === 0 && self::inRange($i % 10, 2, 4) && ! self::inRange($i % 100, 12, 14) || self::inRange($f % 10, 2, 4) && ! self::inRange($f % 100, 12, 14)) {
            return 'few';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the hsb locale.
     */
    private static function getPluralCategoryHsb(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($v === 0 && ($i % 100) === 1 || ($f % 100) === 1) {
            return 'one';
        }

        if ($v === 0 && ($i % 100) === 2 || ($f % 100) === 2) {
            return 'two';
        }

        if ($v === 0 && self::inRange($i % 100, 3, 4) || self::inRange($f % 100, 3, 4)) {
            return 'few';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the hu locale.
     */
    private static function getPluralCategoryHu(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the hy locale.
     */
    private static function getPluralCategoryHy(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($i === 0 || $i === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the ia locale.
     */
    private static function getPluralCategoryIa(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($i === 1 && $v === 0) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the id locale.
     */
    private static function getPluralCategoryId(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        return 'other';
    }

    /**
     * Gets the plural category for the ig locale.
     */
    private static function getPluralCategoryIg(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        return 'other';
    }

    /**
     * Gets the plural category for the ii locale.
     */
    private static function getPluralCategoryIi(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        return 'other';
    }

    /**
     * Gets the plural category for the io locale.
     */
    private static function getPluralCategoryIo(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($i === 1 && $v === 0) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the is locale.
     */
    private static function getPluralCategoryIs(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($t === 0 && ($i % 10) === 1 && ($i % 100) !== 11 || ($t % 10) === 1 && ($t % 100) !== 11) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the it locale.
     */
    private static function getPluralCategoryIt(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($i === 1 && $v === 0) {
            return 'one';
        }

        if ($e === 0 && $i !== 0 && ($i % 1000000) === 0 && $v === 0 || ! self::inRange($e, 0, 5)) {
            return 'many';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the iu locale.
     */
    private static function getPluralCategoryIu(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        if ($n === 2) {
            return 'two';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the ja locale.
     */
    private static function getPluralCategoryJa(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        return 'other';
    }

    /**
     * Gets the plural category for the jbo locale.
     */
    private static function getPluralCategoryJbo(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        return 'other';
    }

    /**
     * Gets the plural category for the jgo locale.
     */
    private static function getPluralCategoryJgo(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the jmc locale.
     */
    private static function getPluralCategoryJmc(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the jv locale.
     */
    private static function getPluralCategoryJv(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        return 'other';
    }

    /**
     * Gets the plural category for the jw locale.
     */
    private static function getPluralCategoryJw(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        return 'other';
    }

    /**
     * Gets the plural category for the ka locale.
     */
    private static function getPluralCategoryKa(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the kab locale.
     */
    private static function getPluralCategoryKab(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($i === 0 || $i === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the kaj locale.
     */
    private static function getPluralCategoryKaj(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the kcg locale.
     */
    private static function getPluralCategoryKcg(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the kde locale.
     */
    private static function getPluralCategoryKde(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        return 'other';
    }

    /**
     * Gets the plural category for the kea locale.
     */
    private static function getPluralCategoryKea(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        return 'other';
    }

    /**
     * Gets the plural category for the kk locale.
     */
    private static function getPluralCategoryKk(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the kkj locale.
     */
    private static function getPluralCategoryKkj(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the kl locale.
     */
    private static function getPluralCategoryKl(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the km locale.
     */
    private static function getPluralCategoryKm(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        return 'other';
    }

    /**
     * Gets the plural category for the kn locale.
     */
    private static function getPluralCategoryKn(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($i === 0 || $n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the ko locale.
     */
    private static function getPluralCategoryKo(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        return 'other';
    }

    /**
     * Gets the plural category for the ks locale.
     */
    private static function getPluralCategoryKs(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the ksb locale.
     */
    private static function getPluralCategoryKsb(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the ksh locale.
     */
    private static function getPluralCategoryKsh(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 0) {
            return 'zero';
        }

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the ku locale.
     */
    private static function getPluralCategoryKu(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the kw locale.
     */
    private static function getPluralCategoryKw(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 0) {
            return 'zero';
        }

        if ($n === 1) {
            return 'one';
        }

        if (
            ($n % 100) === 2 ||
                ($n % 100) === 22 ||
                ($n % 100) === 42 ||
                ($n % 100) === 62 ||
                ($n % 100) === 82 ||
                ($n % 1000) === 0 && (self::inRange($n % 100000, 1000, 20000) || ($n % 100000) === 40000 || ($n % 100000) === 60000 || ($n % 100000) === 80000) ||
                $n !== 0 && ($n % 1000000) === 100000
        ) {
            return 'two';
        }

        if (($n % 100) === 3 || ($n % 100) === 23 || ($n % 100) === 43 || ($n % 100) === 63 || ($n % 100) === 83) {
            return 'few';
        }

        if ($n !== 1 && (($n % 100) === 1 || ($n % 100) === 21 || ($n % 100) === 41 || ($n % 100) === 61 || ($n % 100) === 81)) {
            return 'many';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the ky locale.
     */
    private static function getPluralCategoryKy(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the lag locale.
     */
    private static function getPluralCategoryLag(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 0) {
            return 'zero';
        }

        if (($i === 0 || $i === 1) && $n !== 0) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the lb locale.
     */
    private static function getPluralCategoryLb(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the lg locale.
     */
    private static function getPluralCategoryLg(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the lij locale.
     */
    private static function getPluralCategoryLij(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($i === 1 && $v === 0) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the lkt locale.
     */
    private static function getPluralCategoryLkt(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        return 'other';
    }

    /**
     * Gets the plural category for the lld locale.
     */
    private static function getPluralCategoryLld(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($i === 1 && $v === 0) {
            return 'one';
        }

        if ($e === 0 && $i !== 0 && ($i % 1000000) === 0 && $v === 0 || ! self::inRange($e, 0, 5)) {
            return 'many';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the ln locale.
     */
    private static function getPluralCategoryLn(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if (self::inRange($n, 0, 1)) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the lo locale.
     */
    private static function getPluralCategoryLo(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        return 'other';
    }

    /**
     * Gets the plural category for the lt locale.
     */
    private static function getPluralCategoryLt(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if (($n % 10) === 1 && ! self::inRange($n % 100, 11, 19)) {
            return 'one';
        }

        if (self::inRange($n % 10, 2, 9) && ! self::inRange($n % 100, 11, 19)) {
            return 'few';
        }

        if ($f !== 0) {
            return 'many';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the lv locale.
     */
    private static function getPluralCategoryLv(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if (($n % 10) === 0 || self::inRange($n % 100, 11, 19) || $v === 2 && self::inRange($f % 100, 11, 19)) {
            return 'zero';
        }

        if (($n % 10) === 1 && ($n % 100) !== 11 || $v === 2 && ($f % 10) === 1 && ($f % 100) !== 11 || $v !== 2 && ($f % 10) === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the mas locale.
     */
    private static function getPluralCategoryMas(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the mg locale.
     */
    private static function getPluralCategoryMg(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if (self::inRange($n, 0, 1)) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the mgo locale.
     */
    private static function getPluralCategoryMgo(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the mk locale.
     */
    private static function getPluralCategoryMk(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($v === 0 && ($i % 10) === 1 && ($i % 100) !== 11 || ($f % 10) === 1 && ($f % 100) !== 11) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the ml locale.
     */
    private static function getPluralCategoryMl(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the mn locale.
     */
    private static function getPluralCategoryMn(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the mo locale.
     */
    private static function getPluralCategoryMo(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($i === 1 && $v === 0) {
            return 'one';
        }

        if ($v !== 0 || $n === 0 || $n !== 1 && self::inRange($n % 100, 1, 19)) {
            return 'few';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the mr locale.
     */
    private static function getPluralCategoryMr(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the ms locale.
     */
    private static function getPluralCategoryMs(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        return 'other';
    }

    /**
     * Gets the plural category for the mt locale.
     */
    private static function getPluralCategoryMt(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        if ($n === 2) {
            return 'two';
        }

        if ($n === 0 || self::inRange($n % 100, 3, 10)) {
            return 'few';
        }

        if (self::inRange($n % 100, 11, 19)) {
            return 'many';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the my locale.
     */
    private static function getPluralCategoryMy(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        return 'other';
    }

    /**
     * Gets the plural category for the nah locale.
     */
    private static function getPluralCategoryNah(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the naq locale.
     */
    private static function getPluralCategoryNaq(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        if ($n === 2) {
            return 'two';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the nb locale.
     */
    private static function getPluralCategoryNb(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the nd locale.
     */
    private static function getPluralCategoryNd(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the ne locale.
     */
    private static function getPluralCategoryNe(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the nl locale.
     */
    private static function getPluralCategoryNl(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($i === 1 && $v === 0) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the nn locale.
     */
    private static function getPluralCategoryNn(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the nnh locale.
     */
    private static function getPluralCategoryNnh(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the no locale.
     */
    private static function getPluralCategoryNo(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the nqo locale.
     */
    private static function getPluralCategoryNqo(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        return 'other';
    }

    /**
     * Gets the plural category for the nr locale.
     */
    private static function getPluralCategoryNr(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the nso locale.
     */
    private static function getPluralCategoryNso(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if (self::inRange($n, 0, 1)) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the ny locale.
     */
    private static function getPluralCategoryNy(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the nyn locale.
     */
    private static function getPluralCategoryNyn(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the om locale.
     */
    private static function getPluralCategoryOm(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the or locale.
     */
    private static function getPluralCategoryOr(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the os locale.
     */
    private static function getPluralCategoryOs(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the osa locale.
     */
    private static function getPluralCategoryOsa(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        return 'other';
    }

    /**
     * Gets the plural category for the pa locale.
     */
    private static function getPluralCategoryPa(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if (self::inRange($n, 0, 1)) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the pap locale.
     */
    private static function getPluralCategoryPap(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the pcm locale.
     */
    private static function getPluralCategoryPcm(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($i === 0 || $n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the pl locale.
     */
    private static function getPluralCategoryPl(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($i === 1 && $v === 0) {
            return 'one';
        }

        if ($v === 0 && self::inRange($i % 10, 2, 4) && ! self::inRange($i % 100, 12, 14)) {
            return 'few';
        }

        if ($v === 0 && $i !== 1 && self::inRange($i % 10, 0, 1) || $v === 0 && self::inRange($i % 10, 5, 9) || $v === 0 && self::inRange($i % 100, 12, 14)) {
            return 'many';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the prg locale.
     */
    private static function getPluralCategoryPrg(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if (($n % 10) === 0 || self::inRange($n % 100, 11, 19) || $v === 2 && self::inRange($f % 100, 11, 19)) {
            return 'zero';
        }

        if (($n % 10) === 1 && ($n % 100) !== 11 || $v === 2 && ($f % 10) === 1 && ($f % 100) !== 11 || $v !== 2 && ($f % 10) === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the ps locale.
     */
    private static function getPluralCategoryPs(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the pt locale.
     */
    private static function getPluralCategoryPt(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if (self::inRange($i, 0, 1)) {
            return 'one';
        }

        if ($e === 0 && $i !== 0 && ($i % 1000000) === 0 && $v === 0 || ! self::inRange($e, 0, 5)) {
            return 'many';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the pt-PT locale.
     */
    private static function getPluralCategoryPt_PT(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($i === 1 && $v === 0) {
            return 'one';
        }

        if ($e === 0 && $i !== 0 && ($i % 1000000) === 0 && $v === 0 || ! self::inRange($e, 0, 5)) {
            return 'many';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the rm locale.
     */
    private static function getPluralCategoryRm(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the ro locale.
     */
    private static function getPluralCategoryRo(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($i === 1 && $v === 0) {
            return 'one';
        }

        if ($v !== 0 || $n === 0 || $n !== 1 && self::inRange($n % 100, 1, 19)) {
            return 'few';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the rof locale.
     */
    private static function getPluralCategoryRof(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the ru locale.
     */
    private static function getPluralCategoryRu(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($v === 0 && ($i % 10) === 1 && ($i % 100) !== 11) {
            return 'one';
        }

        if ($v === 0 && self::inRange($i % 10, 2, 4) && ! self::inRange($i % 100, 12, 14)) {
            return 'few';
        }

        if ($v === 0 && ($i % 10) === 0 || $v === 0 && self::inRange($i % 10, 5, 9) || $v === 0 && self::inRange($i % 100, 11, 14)) {
            return 'many';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the rwk locale.
     */
    private static function getPluralCategoryRwk(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the sah locale.
     */
    private static function getPluralCategorySah(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        return 'other';
    }

    /**
     * Gets the plural category for the saq locale.
     */
    private static function getPluralCategorySaq(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the sat locale.
     */
    private static function getPluralCategorySat(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        if ($n === 2) {
            return 'two';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the sc locale.
     */
    private static function getPluralCategorySc(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($i === 1 && $v === 0) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the scn locale.
     */
    private static function getPluralCategoryScn(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($i === 1 && $v === 0) {
            return 'one';
        }

        if ($e === 0 && $i !== 0 && ($i % 1000000) === 0 && $v === 0 || ! self::inRange($e, 0, 5)) {
            return 'many';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the sd locale.
     */
    private static function getPluralCategorySd(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the sdh locale.
     */
    private static function getPluralCategorySdh(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the se locale.
     */
    private static function getPluralCategorySe(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        if ($n === 2) {
            return 'two';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the seh locale.
     */
    private static function getPluralCategorySeh(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the ses locale.
     */
    private static function getPluralCategorySes(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        return 'other';
    }

    /**
     * Gets the plural category for the sg locale.
     */
    private static function getPluralCategorySg(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        return 'other';
    }

    /**
     * Gets the plural category for the sh locale.
     */
    private static function getPluralCategorySh(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($v === 0 && ($i % 10) === 1 && ($i % 100) !== 11 || ($f % 10) === 1 && ($f % 100) !== 11) {
            return 'one';
        }

        if ($v === 0 && self::inRange($i % 10, 2, 4) && ! self::inRange($i % 100, 12, 14) || self::inRange($f % 10, 2, 4) && ! self::inRange($f % 100, 12, 14)) {
            return 'few';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the shi locale.
     */
    private static function getPluralCategoryShi(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($i === 0 || $n === 1) {
            return 'one';
        }

        if (self::inRange($n, 2, 10)) {
            return 'few';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the si locale.
     */
    private static function getPluralCategorySi(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 0 || $n === 1 || $i === 0 && $f === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the sk locale.
     */
    private static function getPluralCategorySk(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($i === 1 && $v === 0) {
            return 'one';
        }

        if (self::inRange($i, 2, 4) && $v === 0) {
            return 'few';
        }

        if ($v !== 0) {
            return 'many';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the sl locale.
     */
    private static function getPluralCategorySl(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($v === 0 && ($i % 100) === 1) {
            return 'one';
        }

        if ($v === 0 && ($i % 100) === 2) {
            return 'two';
        }

        if ($v === 0 && self::inRange($i % 100, 3, 4) || $v !== 0) {
            return 'few';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the sma locale.
     */
    private static function getPluralCategorySma(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        if ($n === 2) {
            return 'two';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the smi locale.
     */
    private static function getPluralCategorySmi(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        if ($n === 2) {
            return 'two';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the smj locale.
     */
    private static function getPluralCategorySmj(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        if ($n === 2) {
            return 'two';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the smn locale.
     */
    private static function getPluralCategorySmn(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        if ($n === 2) {
            return 'two';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the sms locale.
     */
    private static function getPluralCategorySms(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        if ($n === 2) {
            return 'two';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the sn locale.
     */
    private static function getPluralCategorySn(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the so locale.
     */
    private static function getPluralCategorySo(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the sq locale.
     */
    private static function getPluralCategorySq(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the sr locale.
     */
    private static function getPluralCategorySr(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($v === 0 && ($i % 10) === 1 && ($i % 100) !== 11 || ($f % 10) === 1 && ($f % 100) !== 11) {
            return 'one';
        }

        if ($v === 0 && self::inRange($i % 10, 2, 4) && ! self::inRange($i % 100, 12, 14) || self::inRange($f % 10, 2, 4) && ! self::inRange($f % 100, 12, 14)) {
            return 'few';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the ss locale.
     */
    private static function getPluralCategorySs(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the ssy locale.
     */
    private static function getPluralCategorySsy(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the st locale.
     */
    private static function getPluralCategorySt(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the su locale.
     */
    private static function getPluralCategorySu(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        return 'other';
    }

    /**
     * Gets the plural category for the sv locale.
     */
    private static function getPluralCategorySv(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($i === 1 && $v === 0) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the sw locale.
     */
    private static function getPluralCategorySw(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($i === 1 && $v === 0) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the syr locale.
     */
    private static function getPluralCategorySyr(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the ta locale.
     */
    private static function getPluralCategoryTa(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the te locale.
     */
    private static function getPluralCategoryTe(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the teo locale.
     */
    private static function getPluralCategoryTeo(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the th locale.
     */
    private static function getPluralCategoryTh(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        return 'other';
    }

    /**
     * Gets the plural category for the ti locale.
     */
    private static function getPluralCategoryTi(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if (self::inRange($n, 0, 1)) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the tig locale.
     */
    private static function getPluralCategoryTig(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the tk locale.
     */
    private static function getPluralCategoryTk(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the tl locale.
     */
    private static function getPluralCategoryTl(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if (
            $v === 0 && ($i === 1 || $i === 2 || $i === 3) ||
                $v === 0 && ! (($i % 10) === 4 || ($i % 10) === 6 || ($i % 10) === 9) ||
                $v !== 0 && ! (($f % 10) === 4 || ($f % 10) === 6 || ($f % 10) === 9)
        ) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the tn locale.
     */
    private static function getPluralCategoryTn(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the to locale.
     */
    private static function getPluralCategoryTo(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        return 'other';
    }

    /**
     * Gets the plural category for the tpi locale.
     */
    private static function getPluralCategoryTpi(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        return 'other';
    }

    /**
     * Gets the plural category for the tr locale.
     */
    private static function getPluralCategoryTr(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the ts locale.
     */
    private static function getPluralCategoryTs(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the tzm locale.
     */
    private static function getPluralCategoryTzm(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if (self::inRange($n, 0, 1) || self::inRange($n, 11, 99)) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the ug locale.
     */
    private static function getPluralCategoryUg(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the uk locale.
     */
    private static function getPluralCategoryUk(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($v === 0 && ($i % 10) === 1 && ($i % 100) !== 11) {
            return 'one';
        }

        if ($v === 0 && self::inRange($i % 10, 2, 4) && ! self::inRange($i % 100, 12, 14)) {
            return 'few';
        }

        if ($v === 0 && ($i % 10) === 0 || $v === 0 && self::inRange($i % 10, 5, 9) || $v === 0 && self::inRange($i % 100, 11, 14)) {
            return 'many';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the und locale.
     */
    private static function getPluralCategoryUnd(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        return 'other';
    }

    /**
     * Gets the plural category for the ur locale.
     */
    private static function getPluralCategoryUr(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($i === 1 && $v === 0) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the uz locale.
     */
    private static function getPluralCategoryUz(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the ve locale.
     */
    private static function getPluralCategoryVe(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the vec locale.
     */
    private static function getPluralCategoryVec(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($i === 1 && $v === 0) {
            return 'one';
        }

        if ($e === 0 && $i !== 0 && ($i % 1000000) === 0 && $v === 0 || ! self::inRange($e, 0, 5)) {
            return 'many';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the vi locale.
     */
    private static function getPluralCategoryVi(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        return 'other';
    }

    /**
     * Gets the plural category for the vo locale.
     */
    private static function getPluralCategoryVo(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the vun locale.
     */
    private static function getPluralCategoryVun(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the wa locale.
     */
    private static function getPluralCategoryWa(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if (self::inRange($n, 0, 1)) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the wae locale.
     */
    private static function getPluralCategoryWae(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the wo locale.
     */
    private static function getPluralCategoryWo(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        return 'other';
    }

    /**
     * Gets the plural category for the xh locale.
     */
    private static function getPluralCategoryXh(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the xog locale.
     */
    private static function getPluralCategoryXog(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the yi locale.
     */
    private static function getPluralCategoryYi(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($i === 1 && $v === 0) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for the yo locale.
     */
    private static function getPluralCategoryYo(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        return 'other';
    }

    /**
     * Gets the plural category for the yue locale.
     */
    private static function getPluralCategoryYue(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        return 'other';
    }

    /**
     * Gets the plural category for the zh locale.
     */
    private static function getPluralCategoryZh(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        return 'other';
    }

    /**
     * Gets the plural category for the zu locale.
     */
    private static function getPluralCategoryZu(float|int $n): string
    {
        $i = self::getIntegerPart($n);
        $v = self::getVisibleFractionalDigits($n);
        $f = self::getFractionalDigits($n);
        $t = self::getCompactExponent($n);
        $e = self::getExponent($n);

        if ($i === 0 || $n === 1) {
            return 'one';
        }

        return 'other';
    }

    /**
     * Gets the plural category for a number in the specified locale.
     */
    public static function getPluralCategory(Locale $locale, float|int $number): string
    {
        return match ($locale->getLanguage()) {
            'af' => self::getPluralCategoryAf($number),
            'ak' => self::getPluralCategoryAk($number),
            'am' => self::getPluralCategoryAm($number),
            'an' => self::getPluralCategoryAn($number),
            'ar' => self::getPluralCategoryAr($number),
            'ars' => self::getPluralCategoryArs($number),
            'as' => self::getPluralCategoryAs($number),
            'asa' => self::getPluralCategoryAsa($number),
            'ast' => self::getPluralCategoryAst($number),
            'az' => self::getPluralCategoryAz($number),
            'bal' => self::getPluralCategoryBal($number),
            'be' => self::getPluralCategoryBe($number),
            'bem' => self::getPluralCategoryBem($number),
            'bez' => self::getPluralCategoryBez($number),
            'bg' => self::getPluralCategoryBg($number),
            'bho' => self::getPluralCategoryBho($number),
            'blo' => self::getPluralCategoryBlo($number),
            'bm' => self::getPluralCategoryBm($number),
            'bn' => self::getPluralCategoryBn($number),
            'bo' => self::getPluralCategoryBo($number),
            'br' => self::getPluralCategoryBr($number),
            'brx' => self::getPluralCategoryBrx($number),
            'bs' => self::getPluralCategoryBs($number),
            'ca' => self::getPluralCategoryCa($number),
            'ce' => self::getPluralCategoryCe($number),
            'ceb' => self::getPluralCategoryCeb($number),
            'cgg' => self::getPluralCategoryCgg($number),
            'chr' => self::getPluralCategoryChr($number),
            'ckb' => self::getPluralCategoryCkb($number),
            'cs' => self::getPluralCategoryCs($number),
            'csw' => self::getPluralCategoryCsw($number),
            'cy' => self::getPluralCategoryCy($number),
            'da' => self::getPluralCategoryDa($number),
            'de' => self::getPluralCategoryDe($number),
            'doi' => self::getPluralCategoryDoi($number),
            'dsb' => self::getPluralCategoryDsb($number),
            'dv' => self::getPluralCategoryDv($number),
            'dz' => self::getPluralCategoryDz($number),
            'ee' => self::getPluralCategoryEe($number),
            'el' => self::getPluralCategoryEl($number),
            'en' => self::getPluralCategoryEn($number),
            'eo' => self::getPluralCategoryEo($number),
            'es' => self::getPluralCategoryEs($number),
            'et' => self::getPluralCategoryEt($number),
            'eu' => self::getPluralCategoryEu($number),
            'fa' => self::getPluralCategoryFa($number),
            'ff' => self::getPluralCategoryFf($number),
            'fi' => self::getPluralCategoryFi($number),
            'fil' => self::getPluralCategoryFil($number),
            'fo' => self::getPluralCategoryFo($number),
            'fr' => self::getPluralCategoryFr($number),
            'fur' => self::getPluralCategoryFur($number),
            'fy' => self::getPluralCategoryFy($number),
            'ga' => self::getPluralCategoryGa($number),
            'gd' => self::getPluralCategoryGd($number),
            'gl' => self::getPluralCategoryGl($number),
            'gsw' => self::getPluralCategoryGsw($number),
            'gu' => self::getPluralCategoryGu($number),
            'guw' => self::getPluralCategoryGuw($number),
            'gv' => self::getPluralCategoryGv($number),
            'ha' => self::getPluralCategoryHa($number),
            'haw' => self::getPluralCategoryHaw($number),
            'he' => self::getPluralCategoryHe($number),
            'hi' => self::getPluralCategoryHi($number),
            'hnj' => self::getPluralCategoryHnj($number),
            'hr' => self::getPluralCategoryHr($number),
            'hsb' => self::getPluralCategoryHsb($number),
            'hu' => self::getPluralCategoryHu($number),
            'hy' => self::getPluralCategoryHy($number),
            'ia' => self::getPluralCategoryIa($number),
            'id' => self::getPluralCategoryId($number),
            'ig' => self::getPluralCategoryIg($number),
            'ii' => self::getPluralCategoryIi($number),
            'io' => self::getPluralCategoryIo($number),
            'is' => self::getPluralCategoryIs($number),
            'it' => self::getPluralCategoryIt($number),
            'iu' => self::getPluralCategoryIu($number),
            'ja' => self::getPluralCategoryJa($number),
            'jbo' => self::getPluralCategoryJbo($number),
            'jgo' => self::getPluralCategoryJgo($number),
            'jmc' => self::getPluralCategoryJmc($number),
            'jv' => self::getPluralCategoryJv($number),
            'jw' => self::getPluralCategoryJw($number),
            'ka' => self::getPluralCategoryKa($number),
            'kab' => self::getPluralCategoryKab($number),
            'kaj' => self::getPluralCategoryKaj($number),
            'kcg' => self::getPluralCategoryKcg($number),
            'kde' => self::getPluralCategoryKde($number),
            'kea' => self::getPluralCategoryKea($number),
            'kk' => self::getPluralCategoryKk($number),
            'kkj' => self::getPluralCategoryKkj($number),
            'kl' => self::getPluralCategoryKl($number),
            'km' => self::getPluralCategoryKm($number),
            'kn' => self::getPluralCategoryKn($number),
            'ko' => self::getPluralCategoryKo($number),
            'ks' => self::getPluralCategoryKs($number),
            'ksb' => self::getPluralCategoryKsb($number),
            'ksh' => self::getPluralCategoryKsh($number),
            'ku' => self::getPluralCategoryKu($number),
            'kw' => self::getPluralCategoryKw($number),
            'ky' => self::getPluralCategoryKy($number),
            'lag' => self::getPluralCategoryLag($number),
            'lb' => self::getPluralCategoryLb($number),
            'lg' => self::getPluralCategoryLg($number),
            'lij' => self::getPluralCategoryLij($number),
            'lkt' => self::getPluralCategoryLkt($number),
            'lld' => self::getPluralCategoryLld($number),
            'ln' => self::getPluralCategoryLn($number),
            'lo' => self::getPluralCategoryLo($number),
            'lt' => self::getPluralCategoryLt($number),
            'lv' => self::getPluralCategoryLv($number),
            'mas' => self::getPluralCategoryMas($number),
            'mg' => self::getPluralCategoryMg($number),
            'mgo' => self::getPluralCategoryMgo($number),
            'mk' => self::getPluralCategoryMk($number),
            'ml' => self::getPluralCategoryMl($number),
            'mn' => self::getPluralCategoryMn($number),
            'mo' => self::getPluralCategoryMo($number),
            'mr' => self::getPluralCategoryMr($number),
            'ms' => self::getPluralCategoryMs($number),
            'mt' => self::getPluralCategoryMt($number),
            'my' => self::getPluralCategoryMy($number),
            'nah' => self::getPluralCategoryNah($number),
            'naq' => self::getPluralCategoryNaq($number),
            'nb' => self::getPluralCategoryNb($number),
            'nd' => self::getPluralCategoryNd($number),
            'ne' => self::getPluralCategoryNe($number),
            'nl' => self::getPluralCategoryNl($number),
            'nn' => self::getPluralCategoryNn($number),
            'nnh' => self::getPluralCategoryNnh($number),
            'no' => self::getPluralCategoryNo($number),
            'nqo' => self::getPluralCategoryNqo($number),
            'nr' => self::getPluralCategoryNr($number),
            'nso' => self::getPluralCategoryNso($number),
            'ny' => self::getPluralCategoryNy($number),
            'nyn' => self::getPluralCategoryNyn($number),
            'om' => self::getPluralCategoryOm($number),
            'or' => self::getPluralCategoryOr($number),
            'os' => self::getPluralCategoryOs($number),
            'osa' => self::getPluralCategoryOsa($number),
            'pa' => self::getPluralCategoryPa($number),
            'pap' => self::getPluralCategoryPap($number),
            'pcm' => self::getPluralCategoryPcm($number),
            'pl' => self::getPluralCategoryPl($number),
            'prg' => self::getPluralCategoryPrg($number),
            'ps' => self::getPluralCategoryPs($number),
            'pt' => self::getPluralCategoryPt($number),
            'pt-PT' => self::getPluralCategoryPt_PT($number),
            'rm' => self::getPluralCategoryRm($number),
            'ro' => self::getPluralCategoryRo($number),
            'rof' => self::getPluralCategoryRof($number),
            'ru' => self::getPluralCategoryRu($number),
            'rwk' => self::getPluralCategoryRwk($number),
            'sah' => self::getPluralCategorySah($number),
            'saq' => self::getPluralCategorySaq($number),
            'sat' => self::getPluralCategorySat($number),
            'sc' => self::getPluralCategorySc($number),
            'scn' => self::getPluralCategoryScn($number),
            'sd' => self::getPluralCategorySd($number),
            'sdh' => self::getPluralCategorySdh($number),
            'se' => self::getPluralCategorySe($number),
            'seh' => self::getPluralCategorySeh($number),
            'ses' => self::getPluralCategorySes($number),
            'sg' => self::getPluralCategorySg($number),
            'sh' => self::getPluralCategorySh($number),
            'shi' => self::getPluralCategoryShi($number),
            'si' => self::getPluralCategorySi($number),
            'sk' => self::getPluralCategorySk($number),
            'sl' => self::getPluralCategorySl($number),
            'sma' => self::getPluralCategorySma($number),
            'smi' => self::getPluralCategorySmi($number),
            'smj' => self::getPluralCategorySmj($number),
            'smn' => self::getPluralCategorySmn($number),
            'sms' => self::getPluralCategorySms($number),
            'sn' => self::getPluralCategorySn($number),
            'so' => self::getPluralCategorySo($number),
            'sq' => self::getPluralCategorySq($number),
            'sr' => self::getPluralCategorySr($number),
            'ss' => self::getPluralCategorySs($number),
            'ssy' => self::getPluralCategorySsy($number),
            'st' => self::getPluralCategorySt($number),
            'su' => self::getPluralCategorySu($number),
            'sv' => self::getPluralCategorySv($number),
            'sw' => self::getPluralCategorySw($number),
            'syr' => self::getPluralCategorySyr($number),
            'ta' => self::getPluralCategoryTa($number),
            'te' => self::getPluralCategoryTe($number),
            'teo' => self::getPluralCategoryTeo($number),
            'th' => self::getPluralCategoryTh($number),
            'ti' => self::getPluralCategoryTi($number),
            'tig' => self::getPluralCategoryTig($number),
            'tk' => self::getPluralCategoryTk($number),
            'tl' => self::getPluralCategoryTl($number),
            'tn' => self::getPluralCategoryTn($number),
            'to' => self::getPluralCategoryTo($number),
            'tpi' => self::getPluralCategoryTpi($number),
            'tr' => self::getPluralCategoryTr($number),
            'ts' => self::getPluralCategoryTs($number),
            'tzm' => self::getPluralCategoryTzm($number),
            'ug' => self::getPluralCategoryUg($number),
            'uk' => self::getPluralCategoryUk($number),
            'und' => self::getPluralCategoryUnd($number),
            'ur' => self::getPluralCategoryUr($number),
            'uz' => self::getPluralCategoryUz($number),
            've' => self::getPluralCategoryVe($number),
            'vec' => self::getPluralCategoryVec($number),
            'vi' => self::getPluralCategoryVi($number),
            'vo' => self::getPluralCategoryVo($number),
            'vun' => self::getPluralCategoryVun($number),
            'wa' => self::getPluralCategoryWa($number),
            'wae' => self::getPluralCategoryWae($number),
            'wo' => self::getPluralCategoryWo($number),
            'xh' => self::getPluralCategoryXh($number),
            'xog' => self::getPluralCategoryXog($number),
            'yi' => self::getPluralCategoryYi($number),
            'yo' => self::getPluralCategoryYo($number),
            'yue' => self::getPluralCategoryYue($number),
            'zh' => self::getPluralCategoryZh($number),
            'zu' => self::getPluralCategoryZu($number),
            default => 'other',
        };
    }

    /**
     * Gets all supported locales.
     */
    public static function getSupportedLocales(): array
    {
        return [
            'af',
            'ak',
            'am',
            'an',
            'ar',
            'ars',
            'as',
            'asa',
            'ast',
            'az',
            'bal',
            'be',
            'bem',
            'bez',
            'bg',
            'bho',
            'blo',
            'bm',
            'bn',
            'bo',
            'br',
            'brx',
            'bs',
            'ca',
            'ce',
            'ceb',
            'cgg',
            'chr',
            'ckb',
            'cs',
            'csw',
            'cy',
            'da',
            'de',
            'doi',
            'dsb',
            'dv',
            'dz',
            'ee',
            'el',
            'en',
            'eo',
            'es',
            'et',
            'eu',
            'fa',
            'ff',
            'fi',
            'fil',
            'fo',
            'fr',
            'fur',
            'fy',
            'ga',
            'gd',
            'gl',
            'gsw',
            'gu',
            'guw',
            'gv',
            'ha',
            'haw',
            'he',
            'hi',
            'hnj',
            'hr',
            'hsb',
            'hu',
            'hy',
            'ia',
            'id',
            'ig',
            'ii',
            'io',
            'is',
            'it',
            'iu',
            'ja',
            'jbo',
            'jgo',
            'jmc',
            'jv',
            'jw',
            'ka',
            'kab',
            'kaj',
            'kcg',
            'kde',
            'kea',
            'kk',
            'kkj',
            'kl',
            'km',
            'kn',
            'ko',
            'ks',
            'ksb',
            'ksh',
            'ku',
            'kw',
            'ky',
            'lag',
            'lb',
            'lg',
            'lij',
            'lkt',
            'lld',
            'ln',
            'lo',
            'lt',
            'lv',
            'mas',
            'mg',
            'mgo',
            'mk',
            'ml',
            'mn',
            'mo',
            'mr',
            'ms',
            'mt',
            'my',
            'nah',
            'naq',
            'nb',
            'nd',
            'ne',
            'nl',
            'nn',
            'nnh',
            'no',
            'nqo',
            'nr',
            'nso',
            'ny',
            'nyn',
            'om',
            'or',
            'os',
            'osa',
            'pa',
            'pap',
            'pcm',
            'pl',
            'prg',
            'ps',
            'pt',
            'pt-PT',
            'rm',
            'ro',
            'rof',
            'ru',
            'rwk',
            'sah',
            'saq',
            'sat',
            'sc',
            'scn',
            'sd',
            'sdh',
            'se',
            'seh',
            'ses',
            'sg',
            'sh',
            'shi',
            'si',
            'sk',
            'sl',
            'sma',
            'smi',
            'smj',
            'smn',
            'sms',
            'sn',
            'so',
            'sq',
            'sr',
            'ss',
            'ssy',
            'st',
            'su',
            'sv',
            'sw',
            'syr',
            'ta',
            'te',
            'teo',
            'th',
            'ti',
            'tig',
            'tk',
            'tl',
            'tn',
            'to',
            'tpi',
            'tr',
            'ts',
            'tzm',
            'ug',
            'uk',
            'und',
            'ur',
            'uz',
            've',
            'vec',
            'vi',
            'vo',
            'vun',
            'wa',
            'wae',
            'wo',
            'xh',
            'xog',
            'yi',
            'yo',
            'yue',
            'zh',
            'zu',
        ];
    }
}
