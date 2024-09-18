<?php

declare(strict_types=1);

namespace Tempest\Support;

use Countable;

final readonly class StringHelper
{
    public static function title(string $value): string
    {
        return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    }

    public static function lower(string $value): string
    {
        return mb_strtolower($value, 'UTF-8');
    }

    public static function upper(string $value): string
    {
        return mb_strtoupper($value, 'UTF-8');
    }

    public static function snake(string $value, string $delimiter = '_'): string
    {
        if (ctype_lower($value)) {
            return $value;
        }

        $value = preg_replace('/(.)(?=[A-Z])/u', '$1'.$delimiter, $value);
        $value = preg_replace('![^'.preg_quote($delimiter).'\pL\pN\s]+!u', $delimiter, static::lower($value));
        $value = preg_replace('/\s+/u', $delimiter, $value);
        $value = trim($value, $delimiter);

        return static::deduplicate($value, $delimiter);
    }

    public static function kebab(string $value): string
    {
        return static::snake($value, '-');
    }

    public static function pascal(string $value): string
    {
        $words = explode(' ', str_replace(['-', '_'], ' ', $value));
        // TODO: use `mb_ucfirst` when it has landed in PHP 8.4
        $studlyWords = array_map(static fn (string $word) => ucfirst($word), $words);

        return implode('', $studlyWords);
    }

    public static function deduplicate(string $string, string|array $characters = ' '): string
    {
        foreach (ArrayHelper::wrap($characters) as $character) {
            $string = preg_replace('/'.preg_quote($character, '/').'+/u', $character, $string);
        }

        return $string;
    }

    public static function pluralize(string $value, int|array|Countable $count = 2): string
    {
        return LanguageHelper::pluralize($value, $count);
    }

    public static function pluralizeLast(string $value, int|array|Countable $count = 2): string
    {
        $parts = preg_split('/(.)(?=[A-Z])/u', $value, -1, PREG_SPLIT_DELIM_CAPTURE);
        $lastWord = array_pop($parts);

        return implode('', $parts) . self::pluralize($lastWord, $count);
    }

    public static function random(int $length = 16): string
    {
        $string = '';

        while (($len = strlen($string)) < $length) {
            $size = $length - $len;
            $bytesSize = (int) ceil($size / 3) * 3;
            $bytes = random_bytes($bytesSize);
            $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), offset: 0, length: $size);
        }

        return $string;
    }

    public static function finish(string $value, string $cap): string
    {
        return preg_replace('/(?:' . preg_quote($cap, '/') . ')+$/u', replacement: '', subject: $value) . $cap;
    }

    public static function after(string $subject, string|int $search): string
    {
        if ($search === '') {
            return $subject;
        }

        return array_reverse(explode((string) $search, $subject, limit: 2))[0];
    }

    public static function afterLast(string $subject, string|int $search): string
    {
        if ($search === '') {
            return $subject;
        }

        $position = strrpos($subject, (string) $search);

        if ($position === false) {
            return $subject;
        }

        return substr($subject, $position + strlen((string) $search));
    }

    public static function before(string $subject, string|int $search): string
    {
        if ($search === '') {
            return $subject;
        }

        $result = strstr($subject, (string) $search, before_needle: true);

        if ($result === false) {
            return $subject;
        }

        return $result;
    }

    public static function beforeLast(string $subject, string|int $search): string
    {
        if ($search === '') {
            return $subject;
        }

        $pos = mb_strrpos($subject, (string) $search);

        if ($pos === false) {
            return $subject;
        }

        return mb_substr($subject, start: 0, length: $pos);
    }

    public static function between(string $subject, int|string $from, int|string $to): string
    {
        if ($from === '' || $to === '') {
            return $subject;
        }

        return static::beforeLast(static::after($subject, $from), $to);
    }
}
