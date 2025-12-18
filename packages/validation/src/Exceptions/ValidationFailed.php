<?php

declare(strict_types=1);

namespace Tempest\Validation\Exceptions;

use Exception;
use Tempest\Http\Response;
use Tempest\Http\Responses\Invalid;
use Tempest\Router\Exceptions\ConvertsToResponse;
use Tempest\Validation\FailingRule;

final class ValidationFailed extends Exception implements ConvertsToResponse
{
    /**
     * @template TKey of array-key
     *
     * @param array<TKey,FailingRule[]> $failingRules
     * @param array<TKey,string> $errorMessages
     * @param class-string|null $targetClass
     */
    public function __construct(
        private(set) array $failingRules,
        private(set) null|object|string $subject = null,
        private(set) array $errorMessages = [],
        private(set) ?string $targetClass = null,
    ) {
        parent::__construct(match (true) {
            is_null($subject) => 'Validation failed.',
            default => sprintf('Validation failed for %s.', is_object($subject) ? $subject::class : $subject),
        });
    }

    public function convertToResponse(): Response
    {
        return new Invalid($this->subject, $this->failingRules, $this->targetClass);
    }
}
