<?php

namespace Tempest\Intl\MessageFormat\Functions;

use Tempest\Intl\MessageFormat\Formatter\FormattedValue;
use Tempest\Intl\MessageFormat\FormattingFunction;
use Tempest\Intl\MessageFormat\SelectorFunction;
use Tempest\Support\Arr;
use Tempest\Support\Str;

final class StringFunction implements FormattingFunction, SelectorFunction
{
    public string $name = 'string';

    public function match(string $key, mixed $value, array $parameters): bool
    {
        return Str\parse($value, default: '') === $key;
    }

    public function format(mixed $value, array $parameters): FormattedValue
    {
        $string = Str\parse($value, default: '');
        $formatted = match (Arr\get_by_key($parameters, 'style')) {
            'uppercase', 'upper' => Str\to_upper_case($string),
            'lowercase', 'lower' => Str\to_lower_case($string),
            'titlecase', 'title' => Str\to_title_case($string),
            'snakecase', 'snake' => Str\to_snake_case($string),
            'camelcase', 'camel' => Str\to_camel_case($string),
            'kebabcase', 'kebab' => Str\to_kebab_case($string),
            'sentencecase', 'sentence' => Str\to_sentence_case($string),
            default => $string,
        };

        return new FormattedValue($string, $formatted);
    }
}
