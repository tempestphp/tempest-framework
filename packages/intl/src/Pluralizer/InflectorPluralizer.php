<?php

declare(strict_types=1);

namespace Tempest\Intl\Pluralizer;

use Countable;
use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\InflectorFactory;
use Stringable;

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

        // @mago-expect strictness/require-identity-comparison
        if (abs($count) === 1 || preg_match('/^(.*)[A-Za-z0-9\x{0080}-\x{FFFF}]$/u', (string) $value) == 0) {
            return $value;
        }

        return $this->matchCase($this->inflector->pluralize((string) $value), $value);
    }

    public function singularize(Stringable|string $value): string
    {
        return $this->matchCase($this->inflector->singularize((string) $value), $value);
    }

    public function singularizeLastWord(Stringable|string $value): string
    {
        $string = (string) $value;
        $parts = preg_split('/(.)(?=[A-Z])/u', $string, flags: PREG_SPLIT_DELIM_CAPTURE);
        $lastWord = array_pop($parts);

        return implode('', $parts) . $this->singularize($lastWord);
    }

    public function pluralizeLastWord(Stringable|string $value, int|array|Countable $count = 2): string
    {
        $string = (string) $value;
        $parts = preg_split('/(.)(?=[A-Z])/u', $string, flags: PREG_SPLIT_DELIM_CAPTURE);
        $lastWord = array_pop($parts);

        return implode('', $parts) . $this->pluralize($lastWord, $count);
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
