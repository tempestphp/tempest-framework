<?php

declare(strict_types=1);

namespace Tempest\Validation\Exceptions;

use Exception;
use Tempest\Validation\Rule;

final class ValidationFailed extends Exception
{
    /**
     * @template TKey of array-key
     *
     * @param array<TKey,Rule[]> $failingRules
     * @param array<TKey,string> $errorMessages
     * @param class-string|null $targetClass
     */
    public function __construct(
        public readonly array $failingRules,
        public readonly null|object|string $subject = null,
        public readonly array $errorMessages = [],
        public readonly ?string $targetClass = null,
    ) {
        parent::__construct(match (true) {
            is_null($subject) => 'Validation failed.',
            default => sprintf('Validation failed for %s.', is_object($subject) ? $subject::class : $subject),
        });
    }
}
