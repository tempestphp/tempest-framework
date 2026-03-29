<?php

declare(strict_types=1);

namespace Tempest\Intl\Pluralizer;

use Countable;
use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\InflectorFactory;
use Stringable;

use function Tempest\Support\str;

final class InflectorPluralizer implements Pluralizer
{
    private Inflector $inflector;

    public function __construct(string $language = 'english')
    {
        $this->inflector = InflectorFactory::createForLanguage($language)->build();
    }

    public function pluralize(Stringable|string $value, int|array|Countable $count = 2): string
    {
        if (is_countable($count)) {
            $count = count($count);
        }

        // @mago-expect lint:identity-comparison
        if (abs($count) === 1 || preg_match('/^(.*)[A-Za-z0-9\x{0080}-\x{FFFF}]$/u', (string) $value) == 0) {
            return (string) $value;
        }

        return $this->matchCase($this->inflector->pluralize((string) $value), $value);
    }

    public function singularize(Stringable|string $value): string
    {
        return $this->matchCase($this->inflector->singularize((string) $value), $value);
    }

    public function singularizeLastWord(Stringable|string $value): string
    {
        $lastWord = $this->extractLastWord(value: (string) $value, prefix: $prefix, suffix: $suffix);

        if ($lastWord === null) {
            return $suffix;
        }

        return $prefix . $this->singularize(value: $lastWord) . $suffix;
    }

    public function pluralizeLastWord(Stringable|string $value, int|array|Countable $count = 2): string
    {
        $lastWord = $this->extractLastWord(value: (string) $value, prefix: $prefix, suffix: $suffix);

        if ($lastWord === null) {
            return $suffix;
        }

        return $prefix . $this->pluralize(value: $lastWord, count: $count) . $suffix;
    }

    private function extractLastWord(string $value, ?string &$prefix = null, ?string &$suffix = null): ?string
    {
        $parts = $this->splitWords(value: $value);

        $last = end(array: $parts);
        $suffix = $last !== false && str(string: $last)->trim(characters: '_')->isEmpty()
            ? array_pop(array: $parts)
            : '';

        if ($parts === []) {
            return null;
        }

        $lastWord = array_pop(array: $parts);
        $prefix = implode(separator: '', array: $parts);

        return $lastWord;
    }

    /**
     * Splits a string into word segments and underscore delimiters.
     *
     * The regex splits on five boundary types:
     *  - (_+)(?=[a-zA-Z0-9])       — underscore separators followed by a word character (snake_case, SCREAMING_SNAKE)
     *  - (_+)$                     — trailing underscores at end of string
     *  - (?<=[a-z0-9])(?=[A-Z])    — lowercase/digit to uppercase transition (camelCase, PascalCase)
     *  - (?<=[A-Z])(?=[A-Z][a-z])  — end of uppercase run before a new word (HTMLElements → HTML + Elements)
     *  - (?<=\s)(?=\S)             — whitespace to non-whitespace transition (Multiple Aircraft, small dogs)
     *
     * @return list<string>
     */
    private function splitWords(string $value): array
    {
        return (
            preg_split(
                pattern: '/(_+)(?=[a-zA-Z0-9])|(_+)$|(?<=[a-z0-9])(?=[A-Z])|(?<=[A-Z])(?=[A-Z][a-z])|(?<=\s)(?=\S)/u',
                subject: $value,
                flags: PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY,
            ) ?: []
        );
    }

    private function matchCase(Stringable|string $value, Stringable|string $comparison): string
    {
        $value = (string) $value;
        $comparison = (string) $comparison;
        $functions = ['mb_strtolower', 'mb_strtoupper', 'ucfirst', 'ucwords'];

        foreach ($functions as $function) {
            if ($function($comparison) === $comparison) {
                return $function($value);
            }
        }

        return $value;
    }
}
