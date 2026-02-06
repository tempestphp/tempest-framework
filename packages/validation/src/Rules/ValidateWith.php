<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Closure;
use InvalidArgumentException;
use ReflectionFunction;
use Tempest\Validation\Rule;

/**
 * Custom validation rule defined by a closure.
 *
 * The closure receives the value and must return true if it is valid, false otherwise.
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
final readonly class ValidateWith implements Rule
{
    private Closure $callback;

    public function __construct(
        Closure $callback,
    ) {
        $this->callback = $callback;

        $reflection = new ReflectionFunction($callback);

        // Must be static
        if (! $reflection->isStatic()) {
            throw new InvalidArgumentException('Validation closures must be static');
        }

        // Must not capture variables
        if ($reflection->getStaticVariables() !== []) {
            throw new InvalidArgumentException('Validation closures may not capture variables.');
        }
    }

    public function isValid(mixed $value): bool
    {
        return ($this->callback)($value);
    }
}
