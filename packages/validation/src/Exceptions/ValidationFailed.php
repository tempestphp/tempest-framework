<?php

declare(strict_types=1);

namespace Tempest\Validation\Exceptions;

use Exception;
use Tempest\Core\ProvidesContext;
use Tempest\Validation\FailingRule;
use Tempest\Validation\Internal\MessageRule;

final class ValidationFailed extends Exception implements ProvidesContext
{
    /**
     * @template TKey of array-key
     *
     * @param array<TKey,FailingRule[]> $failingRules
     * @param array<TKey,list<string>> $errorMessages
     * @param class-string|null $targetClass
     */
    public function __construct(
        private(set) array $failingRules,
        private(set) object|string|null $subject = null,
        private(set) array $errorMessages = [],
        private(set) ?string $targetClass = null,
    ) {
        $message = match (true) {
            is_null($subject) => 'Validation failed.',
            default => sprintf('Validation failed for %s.', is_object($subject) ? $subject::class : $subject),
        };

        foreach ($errorMessages as $field => $messages) {
            $message .= sprintf('%s- %s: %s', PHP_EOL, $field, implode('; ', $messages));
        }

        parent::__construct($message);
    }

    public function context(): iterable
    {
        return array_filter([
            'errors' => $this->errorMessages,
        ]);
    }

    /**
     * @param array<string,string|list<string>> $messages
     */
    public static function withMessages(array $messages, object|string|null $subject = null, ?string $targetClass = null): self
    {
        $failingRules = [];
        $errorMessages = [];

        foreach ($messages as $field => $messagesForField) {
            $errorMessages[$field] = is_string($messagesForField) ? [$messagesForField] : $messagesForField;

            foreach ($errorMessages[$field] as $message) {
                $failingRules[$field][] = new FailingRule(
                    rule: new MessageRule($message),
                    field: $field,
                );
            }
        }

        return new self(
            failingRules: $failingRules,
            subject: $subject,
            errorMessages: $errorMessages,
            targetClass: $targetClass,
        );
    }
}
