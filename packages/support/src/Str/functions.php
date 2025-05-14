<?php

declare(strict_types=1);

namespace Tempest\Support\Str {
    use Countable;
    use Stringable;
    use Tempest\Support\Arr;
    use Tempest\Support\Language;
    use voku\helper\ASCII;

    use function levenshtein as php_levenshtein;
    use function metaphone as php_metaphone;
    use function strip_tags as php_strip_tags;
    use function Tempest\Support\arr;

    /**
     * Converts the given string to title case.
     */
    function to_title_case(Stringable|string $string): string
    {
        return mb_convert_case((string) $string, mode: MB_CASE_TITLE, encoding: 'UTF-8');
    }

    /**
     * Converts the given string to a naive sentence case. This doesn't detect proper nouns and proper adjectives that should stay capitalized.
     */
    function to_sentence_case(Stringable|string $string): string
    {
        $words = array_map(
            callback: fn (string $string) => to_lower_case($string),
            array: to_words($string),
        );

        return upper_first(implode(' ', $words));
    }

    /**
     * Converts the given string to lower case.
     */
    function to_lower_case(Stringable|string $string): string
    {
        return mb_strtolower((string) $string, encoding: 'UTF-8');
    }

    /**
     * Converts the given string to upper case.
     */
    function to_upper_case(Stringable|string $string): string
    {
        return mb_strtoupper((string) $string, encoding: 'UTF-8');
    }

    /**
     * Converts the given string to snake case.
     */
    function to_snake_case(Stringable|string $string, Stringable|string $delimiter = '_'): string
    {
        $string = (string) $string;
        $delimiter = (string) $delimiter;

        if (ctype_lower($string)) {
            return $string;
        }

        $string = preg_replace('/(.)(?=[A-Z])/u', '$1' . $delimiter, $string);
        $string = preg_replace('![^' . preg_quote($delimiter) . '\pL\pN\s]+!u', $delimiter, mb_strtolower($string, 'UTF-8'));
        $string = preg_replace('/\s+/u', $delimiter, $string);
        $string = trim($string, $delimiter);

        return deduplicate($string, $delimiter);
    }

    /**
     * Returns an array of words from the specified string.
     * This is more accurate than {@see str_word_count()}.
     */
    function to_words(Stringable|string $string): array
    {
        // Remove 'words' that don't consist of alphanumerical characters or punctuation
        $words = trim(preg_replace("#[^(\w|\d|\'|\"|\.|\!|\?|;|,|\\|\/|\-|:|\&|@)]+#", ' ', (string) $string));
        // Remove one-letter 'words' that consist only of punctuation
        $words = trim(preg_replace("#\s*[(\'|\"|\.|\!|\?|;|,|\\|\/|\-|:|\&|@)]\s*#", ' ', $words));

        return array_values(array_filter(explode(' ', namespace\deduplicate($words))));
    }

    /**
     * Counts the number of words in the given string.
     */
    function word_count(Stringable|string $string): int
    {
        return count(to_words($string));
    }

    /**
     * Converts the given string to kebab case.
     */
    function to_kebab_case(Stringable|string $string): string
    {
        return to_snake_case((string) $string, delimiter: '-');
    }

    /**
     * Converts the given string to pascal case.
     */
    function to_pascal_case(Stringable|string $string): string
    {
        $string = (string) $string;
        $words = explode(' ', str_replace(['-', '_'], ' ', $string));
        $studlyWords = array_map(mb_ucfirst(...), $words);

        return implode('', $studlyWords);
    }

    /**
     * Converts the given string to camel case.
     */
    function to_camel_case(Stringable|string $string): string
    {
        return lcfirst(to_pascal_case((string) $string));
    }

    /**
     * Converts the given string to an URL-safe slug.
     *
     * @param bool $replaceSymbols Adds some more replacements e.g. "£" with "pound".
     */
    function to_slug(Stringable|string $string, Stringable|string $separator = '-', array $replacements = [], bool $replaceSymbols = true): string
    {
        return ASCII::to_slugify((string) $string, (string) $separator, replacements: $replacements, replace_extra_symbols: $replaceSymbols);
    }

    /**
     * Transliterates the given string to ASCII.
     *
     * @param string $language Language of the source string. Defaults to english.
     */
    function to_ascii(Stringable|string $string, Stringable|string $language = 'en'): string
    {
        return ASCII::to_ascii((string) $string, (string) $language, replace_single_chars_only: false);
    }

    /**
     * Checks whether the given string is valid ASCII.
     */
    function is_ascii(Stringable|string $string): bool
    {
        return ASCII::is_ascii((string) $string);
    }

    /**
     * Converts the given string to its English plural form.
     */
    function pluralize(Stringable|string $string, int|array|Countable $count = 2): string
    {
        return Language\pluralize((string) $string, $count);
    }

    /**
     * Changes the case of the first letter to uppercase.
     */
    function upper_first(Stringable|string $string): string
    {
        return mb_ucfirst((string) $string);
    }

    /**
     * Changes the case of the first letter to lowercase.
     */
    function lower_first(Stringable|string $string): string
    {
        return mb_lcfirst((string) $string);
    }

    /**
     * Replaces consecutive instances of a given character with a single character.
     */
    function deduplicate(Stringable|string $string, Stringable|string|iterable $characters = ' '): string
    {
        $string = (string) $string;

        foreach (Arr\wrap($characters) as $character) {
            $string = preg_replace('/' . preg_quote($character, '/') . '+/u', $character, $string);
        }

        return $string;
    }

    /**
     * Converts the last word of the given string to its English plural form.
     */
    function pluralize_last_word(Stringable|string $string, int|array|Countable $count = 2): string
    {
        $string = (string) $string;
        $parts = preg_split('/(.)(?=[A-Z])/u', $string, -1, PREG_SPLIT_DELIM_CAPTURE);
        $lastWord = array_pop($parts);

        return implode('', $parts) . pluralize($lastWord, $count);
    }

    /**
     * Converts the last word of the given string to its English plural form.
     */
    function singularize_last_word(Stringable|string $string): string
    {
        $string = (string) $string;
        $parts = preg_split('/(.)(?=[A-Z])/u', $string, -1, PREG_SPLIT_DELIM_CAPTURE);
        $lastWord = array_pop($parts);

        return implode('', $parts) . Language\singularize($lastWord);
    }

    /**
     * Ensures the given string starts with the specified `$prefix`.
     */
    function ensure_starts_with(Stringable|string $string, Stringable|string $prefix): string
    {
        return $prefix . preg_replace('/^(?:' . preg_quote($prefix, '/') . ')+/u', replacement: '', subject: (string) $string);
    }

    /**
     * Ensures the given string ends with the specified `$cap`.
     */
    function ensure_ends_with(Stringable|string $string, Stringable|string $cap): string
    {
        return preg_replace('/(?:' . preg_quote((string) $cap, '/') . ')+$/u', replacement: '', subject: (string) $string) . $cap;
    }

    /**
     * Returns the remainder of the string after the first occurrence of the given value.
     */
    function after_first(Stringable|string $string, Stringable|string|array $search): string
    {
        $string = (string) $string;
        $search = normalize_string($search);

        if ($search === '' || $search === []) {
            return $string;
        }

        $nearestPosition = mb_strlen($string); // Initialize with a large value
        $foundSearch = '';

        foreach (Arr\wrap($search) as $term) {
            $position = mb_strpos($string, $term);

            if ($position !== false && $position < $nearestPosition) {
                $nearestPosition = $position;
                $foundSearch = $term;
            }
        }

        if ($nearestPosition === mb_strlen($string)) {
            return $string;
        }

        return mb_substr($string, $nearestPosition + mb_strlen($foundSearch));
    }

    /**
     * Returns the remainder of the string after the last occurrence of the given value.
     */
    function after_last(Stringable|string $string, Stringable|string|array $search): string
    {
        $string = (string) $string;
        $search = normalize_string($search);

        if ($search === '' || $search === []) {
            return $string;
        }

        $farthestPosition = -1;
        $foundSearch = null;

        foreach (Arr\wrap($search) as $term) {
            $position = mb_strrpos($string, $term);

            if ($position !== false && $position > $farthestPosition) {
                $farthestPosition = $position;
                $foundSearch = $term;
            }
        }

        if ($farthestPosition === -1 || $foundSearch === null) {
            return $string;
        }

        return mb_substr($string, $farthestPosition + strlen($foundSearch));
    }

    /**
     * Returns the portion of the string before the first occurrence of the given value.
     */
    function before_first(Stringable|string $string, Stringable|string|array $search): string
    {
        $string = (string) $string;
        $search = normalize_string($search);

        if ($search === '' || $search === []) {
            return $string;
        }

        $nearestPosition = mb_strlen($string);

        foreach (Arr\wrap($search) as $char) {
            $position = mb_strpos($string, $char);

            if ($position !== false && $position < $nearestPosition) {
                $nearestPosition = $position;
            }
        }

        if ($nearestPosition === mb_strlen($string)) {
            return $string;
        }

        return mb_substr($string, start: 0, length: $nearestPosition);
    }

    /**
     * Returns the portion of the string before the last occurrence of the given value.
     */
    function before_last(Stringable|string $string, Stringable|string|array $search): string
    {
        $string = (string) $string;
        $search = normalize_string($search);

        if ($search === '' || $search === []) {
            return $string;
        }

        $farthestPosition = -1;

        foreach (Arr\wrap($search) as $char) {
            $position = mb_strrpos($string, $char);

            if ($position !== false && $position > $farthestPosition) {
                $farthestPosition = $position;
            }
        }

        if ($farthestPosition === -1) {
            return $string;
        }

        return mb_substr($string, start: 0, length: $farthestPosition);
    }

    /**
     * Returns the multi-bytes length of the string.
     */
    function length(Stringable|string $string): int
    {
        return mb_strlen((string) $string);
    }

    /**
     * Returns the base name of the string, assuming the string is a class name.
     */
    function class_basename(Stringable|string $string): string
    {
        return basename(str_replace('\\', '/', (string) $string));
    }

    /**
     * Asserts whether the string starts with one of the given needles.
     */
    function starts_with(Stringable|string $string, Stringable|string|array $needles): bool
    {
        $string = (string) $string;

        if (! is_array($needles)) {
            $needles = [$needles];
        }

        return array_any($needles, fn ($needle) => str_starts_with($string, (string) $needle));
    }

    /**
     * Asserts whether the string ends with one of the given `$needles`.
     */
    function ends_with(Stringable|string $string, Stringable|string|array $needles): bool
    {
        $string = (string) $string;

        if (! is_array($needles)) {
            $needles = [$needles];
        }

        return array_any($needles, static fn ($needle) => str_ends_with($string, (string) $needle));
    }

    /**
     * Replaces the first occurrence of `$search` with `$replace`.
     */
    function replace_first(Stringable|string $string, array|Stringable|string $search, Stringable|string $replace): string
    {
        $string = (string) $string;
        $search = normalize_string($search);

        foreach (Arr\wrap($search) as $item) {
            if ($search === '') {
                continue;
            }

            $position = strpos($string, (string) $item);

            if ($position === false) {
                continue;
            }

            return substr_replace($string, $replace, $position, strlen($item));
        }

        return $string;
    }

    /**
     * Replaces the last occurrence of `$search` with `$replace`.
     */
    function replace_last(Stringable|string $string, array|Stringable|string $search, Stringable|string $replace): string
    {
        $string = (string) $string;
        $search = normalize_string($search);

        foreach (Arr\wrap($search) as $item) {
            if ($item === '') {
                continue;
            }

            $position = strrpos($string, (string) $item);

            if ($position === false) {
                continue;
            }

            return substr_replace($string, $replace, $position, strlen($item));
        }

        return $string;
    }

    /**
     * Replaces `$search` with `$replace` if `$search` is at the end of the string.
     */
    function replace_end(Stringable|string $string, array|Stringable|string $search, Stringable|string $replace): string
    {
        $string = (string) $string;
        $search = normalize_string($search);

        foreach (Arr\wrap($search) as $item) {
            if ($search === '') {
                continue;
            }

            if (! ends_with($string, $item)) {
                continue;
            }

            return replace_last($string, $item, $replace);
        }

        return $string;
    }

    /**
     * Replaces `$search` with `$replace` if `$search` is at the start of the string.
     */
    function replace_start(Stringable|string $string, array|Stringable|string $search, Stringable|string $replace): string
    {
        $string = (string) $string;

        foreach (Arr\wrap($search) as $item) {
            if ($search === '') {
                continue;
            }

            if (! starts_with($string, $item)) {
                continue;
            }

            return replace_first($string, $item, $replace);
        }

        return $string;
    }

    /**
     * Strips the specified `$prefix` from the start of the string.
     */
    function strip_start(Stringable|string $string, array|Stringable|string $prefix): string
    {
        return replace_start($string, $prefix, '');
    }

    /**
     * Strips the specified `$suffix` from the end of the string.
     */
    function strip_end(Stringable|string $string, array|Stringable|string $suffix): string
    {
        return replace_end($string, $suffix, '');
    }

    /**
     * Replaces the portion of the specified `$length` at the specified `$position` with the specified `$replace`.
     */
    function replace_at(Stringable|string $string, int $position, int $length, Stringable|string $replace): string
    {
        $string = (string) $string;

        if ($length < 0) {
            $position += $length;
            $length = abs($length);
        }

        return substr_replace($string, (string) $replace, $position, $length);
    }

    /**
     * Returns the '$haystack' string with all occurrences of the keys of `$replacements` replaced by the corresponding values.
     *
     * @param array<string, string> $replacements
     */
    function replace_every(Stringable|string $haystack, array $replacements): string
    {
        foreach ($replacements as $needle => $replacement) {
            $haystack = namespace\replace($haystack, $needle, (string) $replacement);
        }

        return $haystack;
    }

    /**
     * Appends the given strings to the string.
     */
    function append(Stringable|string $string, string|Stringable ...$append): string
    {
        return $string . implode('', $append);
    }

    /**
     * Prepends the given strings to the string.
     */
    function prepend(Stringable|string $string, string|Stringable ...$prepend): string
    {
        return implode('', $prepend) . $string;
    }

    /**
     * Returns the portion of the string between the widest possible instances of the given strings.
     */
    function between(Stringable|string $string, string|Stringable $from, string|Stringable $to): string
    {
        $string = (string) $string;
        $from = normalize_string($from);
        $to = normalize_string($to);

        if ($from === '' || $to === '') {
            return $string;
        }

        return before_last(after_first($string, $from), $to);
    }

    /**
     * Wraps the string with the given string. If `$after` is specified, it will be appended instead of `$before`.
     */
    function wrap(Stringable|string $string, string|Stringable $before, string|Stringable|null $after = null): string
    {
        return $before . $string . ($after ??= $before);
    }

    /**
     * Removes the specified `$before` and `$after` from the beginning and the end of the string.
     */
    function unwrap(Stringable|string $string, string|Stringable $before, string|Stringable|null $after = null, bool $strict = true): string
    {
        $string = (string) $string;

        if ($string === '') {
            return $string;
        }

        if ($after === null) {
            $after = $before;
        }

        if (! $strict) {
            return before_last(after_first($string, $before), $after);
        }

        if (starts_with($string, $before) && ends_with($string, $after)) {
            return before_last(after_first($string, $before), $after);
        }

        return $string;
    }

    /**
     * Replaces all occurrences of the given `$search` with `$replace`.
     */
    function replace(Stringable|string $string, Stringable|string|array $search, Stringable|string|array $replace): string
    {
        $string = (string) $string;
        $search = normalize_string($search);
        $replace = normalize_string($replace);

        return str_replace($search, $replace, $string);
    }

    /**
     * Extracts an excerpt from the string.
     */
    function excerpt(Stringable|string $string, int $from, int $to, bool $asArray = false): string|array
    {
        $string = (string) $string;
        $lines = explode(PHP_EOL, $string);

        $from = max(0, $from - 1);
        $to = min($to - 1, count($lines));
        $lines = array_slice($lines, offset: $from, length: ($to - $from) + 1, preserve_keys: true);

        if ($asArray) {
            return arr($lines)
                ->mapWithKeys(fn (string $line, int $number) => yield $number + 1 => $line)
                ->toArray();
        }

        return implode(PHP_EOL, $lines);
    }

    /**
     * Truncates the string to the specified amount of characters.
     */
    function truncate_end(Stringable|string $string, int $characters, Stringable|string $end = ''): string
    {
        $string = (string) $string;
        $end = (string) $end;

        if (mb_strwidth($string, 'UTF-8') <= $characters) {
            return $string;
        }

        if ($characters < 0) {
            $characters = mb_strlen($string) + $characters;
        }

        return rtrim(mb_strimwidth($string, 0, $characters, encoding: 'UTF-8')) . $end;
    }

    /**
     * Truncates the string to the specified amount of characters from the start.
     */
    function truncate_start(Stringable|string $string, int $characters, Stringable|string $start = ''): string
    {
        return reverse(truncate_end(reverse((string) $string), $characters, (string) $start));
    }

    /**
     * Reverses the string.
     */
    function reverse(Stringable|string $string): string
    {
        return implode('', array_reverse(mb_str_split((string) $string, length: 1)));
    }

    /**
     * Gets parts of the string.
     */
    function slice(Stringable|string $string, int $start, ?int $length = null): string
    {
        $stringLength = namespace\length($string);

        if (0 === $start && (null === $length || $stringLength <= $length)) {
            return $string;
        }

        return mb_substr((string) $string, $start, $length);
    }

    /**
     * Checks whether the given string contains the specified `$needle`.
     */
    function contains(Stringable|string $string, Stringable|string|array $needle): bool
    {
        foreach (Arr\wrap($needle) as $item) {
            if (str_contains((string) $string, (string) $item)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Takes the specified amount of characters. If `$length` is negative, starts from the end.
     */
    function take(Stringable|string $string, int $length): string
    {
        $string = (string) $string;

        if ($length < 0) {
            return slice($string, $length);
        }

        return slice($string, 0, $length);
    }

    /**
     * Chunks the string into parts of the specified `$length`.
     */
    function chunk(Stringable|string $string, int $length): array
    {
        $string = (string) $string;

        if ($length <= 0) {
            return [];
        }

        if ($string === '') {
            return [''];
        }

        $chunks = [];

        foreach (str_split($string, $length) as $chunk) {
            $chunks[] = $chunk;
        }

        return $chunks;
    }

    /**
     * Strips HTML and PHP tags from the string.
     */
    function strip_tags(Stringable|string $string, null|string|array $allowed = null): string
    {
        $string = (string) $string;

        $allowed = arr($allowed)
            ->map(fn (string $tag) => wrap($tag, '<', '>'))
            ->toArray();

        return php_strip_tags($string, $allowed);
    }

    /**
     * Pads the string to the given `$width` and centers the text in it.
     */
    function align_center(Stringable|string $string, ?int $width, int $padding = 0): string
    {
        $text = trim((string) $string);
        $textLength = length($text);
        $actualWidth = max($width ?? 0, $textLength + (2 * $padding));
        $leftPadding = (int) floor(($actualWidth - $textLength) / 2);
        $rightPadding = ($actualWidth - $leftPadding) - $textLength;

        return str_repeat(' ', $leftPadding) . $text . str_repeat(' ', $rightPadding);
    }

    /**
     * Pads the string to the given `$width` and aligns the text to the right.
     */
    function align_right(Stringable|string $string, ?int $width, int $padding = 0): string
    {
        $text = trim((string) $string);
        $textLength = length($text);
        $actualWidth = max($width ?? 0, $textLength + (2 * $padding));
        $leftPadding = ($actualWidth - $textLength) - $padding;

        return str_repeat(' ', $leftPadding) . $text . str_repeat(' ', $padding);
    }

    /**
     * Pads the string to the given `$width` and aligns the text to the left.
     */
    function align_left(Stringable|string $string, ?int $width, int $padding = 0): string
    {
        $text = trim((string) $string);
        $textLength = length($text);
        $actualWidth = max($width ?? 0, $textLength + (2 * $padding));
        $rightPadding = ($actualWidth - $textLength) - $padding;

        return str_repeat(' ', $padding) . $text . str_repeat(' ', $rightPadding);
    }

    /**
     * Returns the string padded to the total length by appending the `$pad_string` to the left.
     *
     * If the length of the input string plus the pad string exceeds the total
     * length, the pad string will be truncated. If the total length is less than or
     * equal to the length of the input string, no padding will occur.
     *
     * Example:
     *      pad_left('Ay', 4)
     *      => '  Ay'
     *
     *      pad_left('ay', 3, 'A')
     *      => 'Aay'
     *
     *      pad_left('eet', 4, 'Yeeeee')
     *      => 'Yeet'
     *
     *      pad_left('مرحبا', 8, 'م')
     *      => 'ممممرحبا'
     *
     * @param non-empty-string $padString
     * @param int<0, max> $totalLength
     */
    function pad_left(string $string, int $totalLength, string $padString = ' '): string
    {
        do {
            $length = namespace\length($string);

            if ($length >= $totalLength) {
                return $string;
            }

            /** @var int<0, max> $remaining */
            $remaining = $totalLength - $length;

            if ($remaining <= namespace\length($padString)) {
                $padString = namespace\slice($padString, 0, $remaining);
            }

            $string = $padString . $string;
        } while (true);
    }

    /**
     * Returns the string padded to the total length by appending the `$pad_string` to the right.
     *
     * If the length of the input string plus the pad string exceeds the total
     * length, the pad string will be truncated. If the total length is less than or
     * equal to the length of the input string, no padding will occur.
     *
     * Example:
     *      pad_right('Ay', 4)
     *      => 'Ay  '
     *
     *      pad_right('Ay', 5, 'y')
     *      => 'Ayyyy'
     *
     *      pad_right('Yee', 4, 't')
     *      => 'Yeet'
     *
     *      pad_right('مرحبا', 8, 'ا')
     *      => 'مرحباااا'
     *
     * @param non-empty-string $padString
     * @param int<0, max> $totalLength
     */
    function pad_right(string $string, int $totalLength, string $padString = ' '): string
    {
        do {
            $length = namespace\length($string);

            if ($length >= $totalLength) {
                return $string;
            }

            /** @var int<0, max> $remaining */
            $remaining = $totalLength - $length;

            if ($remaining <= namespace\length($padString)) {
                $padString = namespace\slice($padString, 0, $remaining);
            }

            $string .= $padString;
        } while (true);
    }

    /**
     * Inserts the specified `$insertion` at the specified `$position`.
     */
    function insert_at(Stringable|string $string, int $position, string $insertion): string
    {
        $string = (string) $string;

        return mb_substr($string, 0, $position) . $insertion . mb_substr($string, $position);
    }

    /**
     * Calculates the levenshtein difference between two strings.
     */
    function levenshtein(Stringable|string $string, string|Stringable $other): int
    {
        return php_levenshtein((string) $string, (string) $other);
    }

    /**
     * Calculate the metaphone key of a string.
     */
    function metaphone(Stringable|string $string, int $phonemes = 0): string
    {
        return php_metaphone((string) $string, $phonemes);
    }

    /**
     * Checks whether a string is empty
     */
    function is_empty(Stringable|string $string): bool
    {
        return ((string) $string) === '';
    }

    /**
     * Asserts whether the string is equal to the given string.
     */
    function equals(Stringable|string $string, string|Stringable $other): bool
    {
        return ((string) $string) === ((string) $other);
    }

    /**
     * Normalizes `Stringable` to string, while keeping other values the same.
     *
     * @internal
     */
    function normalize_string(mixed $value): mixed
    {
        if ($value instanceof Stringable) {
            return (string) $value;
        }

        return $value;
    }
}
