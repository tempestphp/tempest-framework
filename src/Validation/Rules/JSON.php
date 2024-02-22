<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\Rule;

/**
 * We need to polyfill this, because at the moment the minimum required version of php for tempest is ^8.2,
 *  and json_validate was introduced in 8.3
 * @TODO remove this polyfill once the minimum php version is >= than 8.3
 * @link https://php.watch/versions/8.3/json_validate#polyfill
 */
if (! function_exists('json_validate')) {
    function json_validate(string $json, int $depth = 512, int $flags = 0): bool
    {
        if ($flags !== 0 && $flags !== \JSON_INVALID_UTF8_IGNORE) {
            throw new \ValueError(
                'json_validate(): Argument #3 ($flags) must be a valid flag (allowed flags: JSON_INVALID_UTF8_IGNORE)'
            );
        }

        if ($depth <= 0) {
            throw new \ValueError('json_validate(): Argument #2 ($depth) must be greater than 0');
        }

        \json_decode($json, null, $depth, $flags);

        return \json_last_error() === \JSON_ERROR_NONE;
    }
}

#[Attribute]
final readonly class JSON implements Rule
{
    public function __construct(
        private ?int $depth = null,
        private ?int $flags = null
    ) {
    }

    public function isValid(mixed $value): bool
    {
        $extraArguments = ['json' => $value];
        if ($this->depth !== null) {
            $extraArguments['depth'] = $this->depth;
        }
        if ($this->flags !== null) {
            $extraArguments['flags'] = $this->flags;
        }

        return json_validate(...$extraArguments);
    }

    public function message(): string
    {
        return 'Value should be a valid JSON string';
    }
}
