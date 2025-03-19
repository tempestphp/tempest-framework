<?php

declare(strict_types=1);

namespace Tempest\Support\Str;

use ArrayAccess;
use Closure;
use Countable;
use Stringable;
use Tempest\Support\Arr\ImmutableArray;
use Tempest\Support\Regex;

use function Tempest\Support\arr;
use function Tempest\Support\Random\secure_string;
use function Tempest\Support\tap;

/**
 * @internal
 */
trait ManipulatesString
{
    private(set) string $value;

    public function __construct(Stringable|int|string|null $string = '')
    {
        $this->value = (string) ($string ?? '');
    }

    /**
     * Returns a new instance with the specified string,
     * or mutates the instance if this is a `MutableString`.
     */
    abstract protected function createOrModify(Stringable|string $string): static;

    /**
     * Prefixes the instance with the given string.
     */
    public function start(Stringable|string $prefix): static
    {
        return $this->createOrModify(ensure_starts_with($this->value, $prefix));
    }

    /**
     * Caps the string with the given string.
     */
    public function finish(Stringable|string $cap): static
    {
        return $this->createOrModify(ensure_ends_with($this->value, $cap));
    }

    /**
     * Returns the remainder of the string after the first occurrence of the given value.
     */
    public function afterFirst(Stringable|string|array $search): static
    {
        return $this->createOrModify(after_first($this->value, $search));
    }

    /**
     * Returns the remainder of the string after the last occurrence of the given value.
     */
    public function afterLast(Stringable|string|array $search): static
    {
        return $this->createOrModify(after_last($this->value, $search));
    }

    /**
     * Returns the portion of the string before the first occurrence of the given value.
     */
    public function before(Stringable|string|array $search): static
    {
        return $this->createOrModify(before_first($this->value, $search));
    }

    /**
     * Returns the portion of the string before the last occurrence of the given value.
     */
    public function beforeLast(Stringable|string|array $search): static
    {
        return $this->createOrModify(before_last($this->value, $search));
    }

    /**
     * Returns the portion of the string between the widest possible instances of the given strings.
     */
    public function between(string|Stringable $from, string|Stringable $to): static
    {
        return $this->createOrModify(between($this->value, $from, $to));
    }

    /**
     * Removes all whitespace (or specified characters) from both ends of the instance.
     */
    public function trim(string $characters = " \n\r\t\v\0"): static
    {
        return $this->createOrModify(trim($this->value, $characters));
    }

    /**
     * Removes all whitespace (or specified characters) from the start of the instance.
     */
    public function ltrim(string $characters = " \n\r\t\v\0"): static
    {
        return $this->createOrModify(ltrim($this->value, $characters));
    }

    /**
     * Removes all whitespace (or specified characters) from the end of the instance.
     */
    public function rtrim(string $characters = " \n\r\t\v\0"): static
    {
        return $this->createOrModify(rtrim($this->value, $characters));
    }

    /**
     * Converts the string to its English plural form.
     */
    public function pluralize(int|array|Countable $count = 2): static
    {
        return $this->createOrModify(pluralize($this->value, $count));
    }

    /**
     * Converts the last word to its English plural form.
     */
    public function pluralizeLastWord(int|array|Countable $count = 2): static
    {
        return $this->createOrModify(pluralize_last_word($this->value, $count));
    }

    /**
     * Creates a pseudo-random alpha-numeric string of the given length.
     */
    public function random(int $length = 16): static
    {
        return $this->createOrModify(secure_string($length));
    }

    /**
     * Converts the string to title case.
     */
    public function title(): static
    {
        return $this->createOrModify(to_title_case($this->value));
    }

    /**
     * Converts the instance to snake case.
     */
    public function snake(Stringable|string $delimiter = '_'): static
    {
        return $this->createOrModify(to_snake_case($this->value, $delimiter));
    }

    /**
     * Converts the instance to kebab case.
     */
    public function kebab(): static
    {
        return $this->createOrModify(to_kebab_case($this->value));
    }

    /**
     * Converts the instance to pascal case.
     */
    public function pascal(): static
    {
        return $this->createOrModify(to_pascal_case($this->value));
    }

    /**
     * Converts the instance to camel case.
     */
    public function camel(): static
    {
        return $this->createOrModify(to_camel_case($this->value));
    }

    /**
     * Replaces consecutive instances of a given character with a single character.
     */
    public function deduplicate(Stringable|string|ArrayAccess|array $characters = ' '): static
    {
        return $this->createOrModify(deduplicate($this->value, $characters));
    }

    /**
     * Converts the instance to lower case.
     */
    public function lower(): static
    {
        return $this->createOrModify(to_lower_case($this->value));
    }

    /**
     * Converts the instance to upper case.
     */
    public function upper(): static
    {
        return $this->createOrModify(to_upper_case($this->value));
    }

    /**
     * Changes the case of the first letter to uppercase.
     */
    public function upperFirst(): static
    {
        return $this->createOrModify(upper_first($this->value));
    }

    /**
     * Changes the case of the first letter to lowercase.
     */
    public function lowerFirst(): static
    {
        return $this->createOrModify(lower_first($this->value));
    }

    /**
     * Keeps only the base name of the instance.
     */
    public function basename(string $suffix = ''): static
    {
        return $this->createOrModify(basename($this->value, $suffix));
    }

    /**
     * Keeps only the base name of the instance, assuming the instance is a class name.
     */
    public function classBasename(): static
    {
        return $this->createOrModify(class_basename($this->value));
    }

    /**
     * Replaces the first occurrence of `$search` with `$replace`.
     */
    public function replaceFirst(Stringable|string $search, Stringable|string $replace): static
    {
        return $this->createOrModify(replace_first($this->value, $search, $replace));
    }

    /**
     * Replaces the last occurrence of `$search` with `$replace`.
     */
    public function replaceLast(Stringable|string $search, Stringable|string $replace): static
    {
        return $this->createOrModify(replace_last($this->value, $search, $replace));
    }

    /**
     * Replaces `$search` with `$replace` if `$search` is at the end of the instance.
     */
    public function replaceEnd(Stringable|string $search, Stringable|string $replace): static
    {
        return $this->createOrModify(replace_end($this->value, $search, $replace));
    }

    /**
     * Replaces `$search` with `$replace` if `$search` is at the start of the instance.
     */
    public function replaceStart(Stringable|string $search, Stringable|string $replace): static
    {
        return $this->createOrModify(replace_start($this->value, $search, $replace));
    }

    /**
     * Strips the specified `$prefix` from the start of the string.
     */
    public function stripStart(Stringable|string $prefix): static
    {
        return $this->createOrModify(strip_start($this->value, $prefix));
    }

    /**
     * Strips the specified `$suffix` from the end of the string.
     */
    public function stripEnd(Stringable|string $suffix): static
    {
        return $this->createOrModify(strip_end($this->value, $suffix));
    }

    /**
     * Replaces the portion of the specified `$length` at the specified `$position` with the specified `$replace`.
     *
     * ### Example
     * ```php
     * str('Lorem dolor')->replaceAt(6, 5, 'ipsum'); // Lorem ipsum
     * ```
     */
    public function replaceAt(int $position, int $length, Stringable|string $replace): static
    {
        return $this->createOrModify(replace_at($this->value, $position, $length, $replace));
    }

    /**
     * Appends the given strings to the instance.
     */
    public function append(string|Stringable ...$append): static
    {
        return $this->createOrModify(append($this->value, ...$append));
    }

    /**
     * Prepends the given strings to the instance.
     */
    public function prepend(string|Stringable ...$prepend): static
    {
        return $this->createOrModify(prepend($this->value, ...$prepend));
    }

    /**
     * Wraps the instance with the given string. If `$after` is specified, it will be appended instead of `$before`.
     *
     * ### Example
     * ```php
     * str('Scott')->wrap(before: 'Leon ', after: ' Kennedy'); // Leon Scott Kennedy
     * ```
     */
    public function wrap(string|Stringable $before, string|Stringable|null $after = null): static
    {
        return $this->createOrModify(wrap($this->value, $before, $after));
    }

    /**
     * Removes the specified `$before` and `$after` from the beginning and the end of the instance. If `$after` is null, `$before` is used instead.
     * Setting `$strict` to `false` will unwrap the instance even if both ends do not correspond to the specified `$before` and `$after`.
     *
     * ### Example
     * ```php
     *  str('Scott Kennedy')->unwrap(before: 'Leon ', after: ' Kennedy', strict: false); // Scott
     * ```
     */
    public function unwrap(string|Stringable $before, string|Stringable|null $after = null, bool $strict = true): static
    {
        return $this->createOrModify(unwrap($this->value, $before, $after, $strict));
    }

    /**
     * Extracts an excerpt from the instance.
     */
    public function excerpt(int $from, int $to, bool $asArray = false): static|ImmutableArray
    {
        $value = excerpt($this->value, $from, $to, $asArray);

        if ($asArray) {
            return new ImmutableArray($value);
        }

        return $this->createOrModify($value);
    }

    /**
     * Truncates the instance to the specified amount of characters.
     *
     * ### Example
     * ```php
     * str('Lorem ipsum')->truncate(5, end: '...'); // Lorem...
     * ```
     */
    public function truncate(int $characters, Stringable|string $end = ''): static
    {
        return $this->createOrModify(truncate_end($this->value, $characters, $end));
    }

    /**
     * Truncates the instance to the specified amount of characters from the start.
     *
     * ### Example
     * ```php
     * str('Lorem ipsum')->truncateStart(5, start: '...'); // ...ipsum
     * ```
     */
    public function truncateStart(int $characters, string $start = ''): static
    {
        return $this->createOrModify(truncate_start($this->value, $characters, $start));
    }

    /**
     * Reverses the instance.
     *
     * ### Example
     * ```php
     * str('Lorem ipsum')->reverse(); // muspi meroL
     * ```
     */
    public function reverse(): static
    {
        return $this->createOrModify(reverse($this->value));
    }

    /**
     * Gets parts of the instance.
     *
     * ### Example
     * ```php
     * str('Lorem ipsum')->substr(0, length: 5); // Lorem
     * str('Lorem ipsum')->substr(6); // ipsum
     * ```
     */
    public function substr(int $start, ?int $length = null): static
    {
        return $this->createOrModify(mb_substr($this->value, $start, $length));
    }

    /**
     * Takes the specified amount of characters. If `$length` is negative, starts from the end.
     */
    public function take(int $length): static
    {
        return $this->createOrModify(take($this->value, $length));
    }

    /**
     * Strips HTML and PHP tags from the instance.
     *
     * @param null|string|string[] $allowed Allowed tags.
     *
     * ### Example
     * ```php
     * str('<p>Lorem ipsum</p>')->stripTags(); // Lorem ipsum
     * str('<p>Lorem <strong>ipsum</strong></p>')->stripTags(allowed: 'strong'); // Lorem <strong>ipsum</strong>
     * ```
     */
    public function stripTags(null|string|array $allowed = null): static
    {
        return $this->createOrModify(strip_tags($this->value, $allowed));
    }

    /**
     * Pads the instance to the given `$width` and centers the text in it.
     *
     * ### Example
     * ```php
     * str('Lorem ipsum')->alignCenter(width: 20);
     * ```
     */
    public function alignCenter(?int $width, int $padding = 0): static
    {
        return $this->createOrModify(align_center($this->value, $width, $padding));
    }

    /**
     * Pads the instance to the given `$width` and aligns the text to the right.
     *
     * ### Example
     * ```php
     * str('Lorem ipsum')->alignRight(width: 20);
     * ```
     */
    public function alignRight(?int $width, int $padding = 0): static
    {
        return $this->createOrModify(align_right($this->value, $width, $padding));
    }

    /**
     * Pads the instance to the given `$width` and aligns the text to the left.
     *
     * ### Example
     * ```php
     * str('Lorem ipsum')->alignLeft(width: 20);
     * ```
     */
    public function alignLeft(?int $width, int $padding = 0): static
    {
        return $this->createOrModify(align_left($this->value, $width, $padding));
    }

    /**
     * Inserts the specified `$string` at the specified `$position`.
     *
     * ### Example
     * ```php
     * str('Lorem ipsum sit amet')->insertAt(11, ' dolor'); // Lorem ipsum dolor sit amet
     * ```
     */
    public function insertAt(int $position, string $string): static
    {
        return $this->createOrModify(insert_at($this->value, $position, $string));
    }

    /**
     * Chunks the instance into parts of the specified `$length`.
     */
    public function chunk(int $length): ImmutableArray
    {
        return new ImmutableArray(chunk($this->value, $length));
    }

    /**
     * Explodes the string into an {@see \Tempest\Support\Arr\ImmutableArray} instance by a separator.
     */
    public function explode(string $separator = ' '): ImmutableArray
    {
        return new ImmutableArray(explode($separator, $this->value));
    }

    /**
     * Formats the string.
     */
    public function format(mixed ...$args): static
    {
        return $this->createOrModify(vsprintf($this->value, $args));
    }

    /**
     * Replaces all occurrences of the given `$search` with `$replace`.
     */
    public function replace(Stringable|string|array $search, Stringable|string|array $replace): static
    {
        return $this->createOrModify(replace($this->value, $search, $replace));
    }

    /**
     * Removes all occurrences of the given `$search`.
     */
    public function erase(Stringable|string|array $search): static
    {
        return $this->createOrModify(replace($this->value, $search, ''));
    }

    /**
     * Replaces the patterns matching the given regular expression.
     */
    public function replaceRegex(array|string $regex, array|string|callable $replace): static
    {
        return $this->createOrModify(Regex\replace($this->value, $regex, $replace));
    }

    /**
     * Gets the first portion of the instance that matches the given regular expression.
     *
     * ### Example
     * ```php
     * str('10-abc')->match('/(?<id>\d+-)/'); // ['id' => '10']
     * ```
     */
    public function match(string $regex): array
    {
        return Regex\get_first_match($this->value, $regex);
    }

    /**
     * Gets all portions of the instance that match the given regular expression.
     */
    public function matchAll(string $regex, int $flags = 0, int $offset = 0): array
    {
        return Regex\get_all_matches($this->value, $regex, $flags, $offset);
    }

    /**
     * Asserts whether the instance matches the given regular expression.
     *
     * ### Example
     * ```php
     * str('Lorem ipsum')->matches('/ipsum/'); // true
     * ```
     */
    public function matches(string $regex): bool
    {
        return Regex\matches($this->value, $regex);
    }

    /**
     * Checks whether this string contains another string
     *
     * ### Example
     * ```php
     * str('Lorem ipsum')->contains('ipsum'); // true
     * str('Lorem ipsum')->contains('something else'); // false
     * ```
     */
    public function contains(string|Stringable $needle): bool
    {
        return contains($this->value, (string) $needle);
    }

    /**
     * Asserts whether the instance starts with one of the given needles.
     */
    public function startsWith(Stringable|string|array $needles): bool
    {
        return starts_with($this->value, $needles);
    }

    /**
     * Asserts whether the instance ends with one of the given `$needles`.
     */
    public function endsWith(Stringable|string|array $needles): bool
    {
        return ends_with($this->value, $needles);
    }

    /**
     * Calculates the levenshtein difference between this instance and the specified string.
     */
    public function levenshtein(Stringable|string $string): int
    {
        return levenshtein($this->value, $string);
    }

    /**
     * Implodes the given array into a string by a separator.
     */
    public static function implode(ArrayAccess|array $parts, string $glue = ' '): static
    {
        return new static(arr($parts)->implode($glue));
    }

    /**
     * Joins all values using the specified `$glue`. The last item of the string is separated by `$finalGlue`.
     */
    public static function join(ArrayAccess|array $parts, string $glue = ', ', ?string $finalGlue = ' and '): static
    {
        return new static(arr($parts)->join($glue, $finalGlue));
    }

    /**
     * Check whether the string is not empty.
     */
    public function isNotEmpty(): bool
    {
        return ! $this->isEmpty();
    }

    /**
     * Check whether the string is empty.
     */
    public function isEmpty(): bool
    {
        return is_empty($this->value);
    }

    /**
     * Asserts whether the instance is equal to the given instance or string.
     */
    public function equals(string|Stringable $other): bool
    {
        return $this->value === ((string) $other);
    }

    /**
     * Returns the multi-bytes length of the instance.
     */
    public function length(): int
    {
        return mb_strlen($this->value);
    }

    /**
     * Executes callback with the given `$value` and returns the same `$value`.
     *
     * @param (Closure(static): void) $callback
     */
    public function tap(Closure $callback): static
    {
        tap($this, $callback);

        return $this;
    }

    /**
     * Dumps the instance and stops the execution of the script.
     */
    public function dd(mixed ...$dd): void
    {
        ld($this->value, ...$dd);
    }

    /**
     * Dumps the instance.
     */
    public function dump(mixed ...$dumps): static
    {
        lw($this->value, ...$dumps);

        return $this;
    }

    /**
     * Converts to a scalar string.
     */
    public function toString(): string
    {
        return $this->__toString();
    }

    /**
     * Converts to a JSON serializable string.
     */
    public function jsonSerialize(): string
    {
        return $this->__toString();
    }

    /**
     * Converts to a scalar string.
     */
    public function __toString(): string
    {
        return $this->value;
    }

    public static function __set_state(array $array): object
    {
        return new self($array['value']);
    }
}
