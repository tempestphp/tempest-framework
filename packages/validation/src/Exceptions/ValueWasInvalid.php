<?php

declare(strict_types=1);

namespace Tempest\Validation\Exceptions;

use Exception;

final class ValueWasInvalid extends Exception
{
    public function __construct(
        public readonly mixed $value,
        /** @var Rule[] $failingRules */
        public readonly array $failingRules,
    ) {}
}
