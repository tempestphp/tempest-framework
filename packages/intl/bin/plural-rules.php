#!/usr/bin/env php
<?php

/**
 * This files generates the `src/PluralRules/PluralRulesMatcher.php` class.
 * This class holds the compiled logic for determining plural categories based on the CLDR dataset.
 */

require_once getcwd() . '/vendor/autoload.php';

use Tempest\Console\Console;
use Tempest\Console\ConsoleApplication;
use Tempest\Support\Filesystem;
use Tempest\Support\Json;

use function Tempest\get;

final class PluralRulesMatcherGenerator
{
    public function __construct(
        private array $data,
        private string $className,
    ) {}

    public function generate(): string
    {
        $output = "<?php\n\n";
        $output .= "namespace Tempest\Intl\PluralRules;\n\n";
        $output .= "use Tempest\Intl\Locale;\n\n";
        $output .= "/**\n";
        $output .= " * This file was auto-generated using the plural rules CLDR dataset.\n";
        $output .= ' * Generated on: ' . date('Y-m-d H:i:s') . "\n";
        $output .= " */\n";
        $output .= "final class {$this->className}\n{\n";
        $output .= $this->generateHelperMethods();

        $pluralRules = $this->data['supplemental']['plurals-type-cardinal'] ?? [];
        foreach ($pluralRules as $locale => $rules) {
            $output .= $this->generateLanguageMethod($locale, $rules);
        }

        $output .= $this->generateDispatcherMethod(array_keys($pluralRules));
        $output .= "}\n";

        return $output;
    }

    private function generateHelperMethods(): string
    {
        return <<<'PHP'
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

                if (!str_contains($str, '.')) {
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

                if (!str_contains($str, '.')) {
                    return 0;
                }

                return (int) rtrim(explode('.', $str)[1], '0') ?: 0;
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
                    } elseif ((float) $part === (float) $value) {
                        return true;
                    }
                }
                return false;
            }

        PHP;
    }

    private function generateLanguageMethod(string $locale, array $rules): string
    {
        $methodName = 'getPluralCategory' . ucfirst(str_replace('-', '_', $locale));

        $output = "    /**\n";
        $output .= "     * Gets the plural category for the {$locale} locale.\n";
        $output .= "     */\n";
        $output .= "    private static function {$methodName}(float|int \$n): string\n";
        $output .= "    {\n";
        $output .= "        \$i = self::getIntegerPart(\$n);\n";
        $output .= "        \$v = self::getVisibleFractionalDigits(\$n);\n";
        $output .= "        \$f = self::getFractionalDigits(\$n);\n";
        $output .= "        \$t = self::getCompactExponent(\$n);\n";
        $output .= "        \$e = self::getExponent(\$n);\n\n";

        $priority = ['zero', 'one', 'two', 'few', 'many', 'other'];
        $sortedRules = [];

        foreach ($priority as $category) {
            $ruleKey = "pluralRule-count-{$category}";

            if (isset($rules[$ruleKey])) {
                $sortedRules[$category] = $rules[$ruleKey];
            }
        }

        foreach ($sortedRules as $category => $rule) {
            if ($category === 'other') {
                $output .= "        return '{$category}';\n";
                break;
            }

            if ($condition = $this->parseRule($rule)) {
                $output .= "        if ({$condition}) {\n";
                $output .= "            return '{$category}';\n";
                $output .= "        }\n\n";
            }
        }

        $output .= "    }\n\n";

        return $output;
    }

    private function parseRule(string $rule): string
    {
        $rulePart = trim(explode('@', $rule)[0]);

        if (! $rulePart) {
            return '';
        }

        return $this->parseCondition($rulePart);
    }

    private function parseCondition(string $condition): string
    {
        $condition = trim($condition);

        if (str_contains($condition, ' or ')) {
            $orParts = explode(' or ', $condition);
            $parsedParts = array_map([$this, 'parseCondition'], $orParts);
            return '(' . implode(') || (', $parsedParts) . ')';
        }

        if (str_contains($condition, ' and ')) {
            $andParts = explode(' and ', $condition);
            $parsedParts = array_map([$this, 'parseCondition'], $andParts);
            return '(' . implode(') && (', $parsedParts) . ')';
        }

        return $this->parseSingleCondition($condition);
    }

    private function parseSingleCondition(string $condition): string
    {
        $condition = trim($condition);

        if (preg_match('/^([nifvet])\s*%\s*(\d+)\s*(=|!=)\s*(.+)$/', $condition, $matches)) {
            $var = $this->getVariable($matches[1]);
            $mod = $matches[2];
            $op = $matches[3] === '=' ? '===' : '!==';
            $values = $matches[4];

            return $this->parseValueCondition("({$var} % {$mod})", $op, $values);
        }

        if (preg_match('/^([nifvet])\s*(=|!=)\s*(.+)$/', $condition, $matches)) {
            $var = $this->getVariable($matches[1]);
            $op = $matches[2] === '=' ? '===' : '!==';
            $values = $matches[3];

            return $this->parseValueCondition($var, $op, $values);
        }

        return $condition;
    }

    private function parseValueCondition(string $varExpression, string $operator, string $values): string
    {
        $values = trim($values);
        $isNegative = $operator === '!==';

        if (preg_match('/^\d+(?:\.\d+)?$/', $values)) {
            return "{$varExpression} {$operator} {$values}";
        }

        if (preg_match('/^(\d+(?:\.\d+)?)\.\.(\d+(?:\.\d+)?)$/', $values, $matches)) {
            $start = $matches[1];
            $end = $matches[2];
            $condition = "self::inRange({$varExpression}, {$start}, {$end})";
            return $isNegative ? "!{$condition}" : $condition;
        }

        if (str_contains($values, ',')) {
            $parts = array_map('trim', explode(',', $values));
            $conditions = [];

            foreach ($parts as $part) {
                if (str_contains($part, '..')) {
                    if (preg_match('/^(\d+(?:\.\d+)?)\.\.(\d+(?:\.\d+)?)$/', $part, $matches)) {
                        $start = $matches[1];
                        $end = $matches[2];
                        $conditions[] = "self::inRange({$varExpression}, {$start}, {$end})";
                    }
                } elseif (str_contains($part, '~')) {
                    if (preg_match('/^(\d+(?:\.\d+)?)~(\d+(?:\.\d+)?)$/', $part, $matches)) {
                        $start = $matches[1];
                        $end = $matches[2];
                        $conditions[] = "self::inRange({$varExpression}, {$start}, {$end})";
                    }
                } else {
                    $conditions[] = "{$varExpression} === {$part}";
                }
            }

            if (! $conditions) {
                return 'false';
            }

            $combined = '(' . implode(' || ', $conditions) . ')';
            return $isNegative ? "!{$combined}" : $combined;
        }

        if (str_contains($values, '~')) {
            if (preg_match('/^(\d+(?:\.\d+)?)~(\d+(?:\.\d+)?)$/', $values, $matches)) {
                $start = $matches[1];
                $end = $matches[2];
                $condition = "self::inRange({$varExpression}, {$start}, {$end})";
                return $isNegative ? "!{$condition}" : $condition;
            }
        }

        $condition = "self::matchesValues({$varExpression}, '{$values}')";

        return $isNegative ? "!{$condition}" : $condition;
    }

    private function getVariable(string $var): string
    {
        return match ($var) {
            'n' => '$n',
            'i' => '$i',
            'v' => '$v',
            'f' => '$f',
            't' => '$t',
            'e' => '$e',
            default => '$n',
        };
    }

    private function generateDispatcherMethod(array $locales): string
    {
        $output = "    /**\n";
        $output .= "     * Gets the plural category for a number in the specified locale.\n";
        $output .= "     */\n";
        $output .= "    public static function getPluralCategory(Locale \$locale, float|int \$number): string\n";
        $output .= "    {\n";
        $output .= "        return match(\$locale->getLanguage()) {\n";

        foreach ($locales as $locale) {
            $methodName = 'getPluralCategory' . ucfirst(str_replace('-', '_', $locale));
            $output .= "            '{$locale}' => self::{$methodName}(\$number),\n";
        }

        $output .= "            default => 'other'\n";
        $output .= "        };\n";
        $output .= "    }\n\n";

        $output .= "    /**\n";
        $output .= "     * Gets all supported locales.\n";
        $output .= "     */\n";
        $output .= "    public static function getSupportedLocales(): array\n";
        $output .= "    {\n";
        $output .= "        return ['" . implode("', '", $locales) . "'];\n";
        $output .= "    }\n\n";

        return $output;
    }
}

// ---

ConsoleApplication::boot();

$className = 'PluralRulesMatcher';
$data = Json\decode(file_get_contents('https://raw.githubusercontent.com/unicode-org/cldr-json/refs/heads/main/cldr-json/cldr-core/supplemental/plurals.json'));

Filesystem\delete($target = __DIR__ . "/../src/PluralRules/{$className}.php");
Filesystem\write_file($target, new PluralRulesMatcherGenerator($data, $className)->generate());

$console = get(Console::class);
$console->writeln();
$console->success("Generated <file='{$target}'/>");
