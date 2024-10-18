<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Closure;
use Tempest\Validation\Rule;

#[Attribute(Attribute::IS_REPEATABLE)]
final readonly class Callback implements Rule
{
    public function __construct(
        private Closure $callable,
        private string $message = '',
    ) {
    }

    public function isValid(mixed $value): bool
    {
        return call_user_func($this->callable, $value);
    }

    public function message(): string
    {
        return $this->message;
    }
}
