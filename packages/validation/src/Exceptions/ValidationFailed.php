<?php

declare(strict_types=1);

namespace Tempest\Validation\Exceptions;

use Exception;
use Tempest\Validation\Rule;

use function Tempest\Support\arr;

final class ValidationFailed extends Exception
{
    public function __construct(
        public readonly object|string $subject,
        public readonly array $failingRules,
    ) {
        $messages = [];

        foreach ($this->failingRules as $field => $failingRulesForField) {
            /** @var Rule $failingRuleForField */
            foreach ($failingRulesForField as $failingRuleForField) {
                $messages[$field][] = arr($failingRuleForField->message())->join()->toString();
            }
        }

        if (is_object($subject)) {
            $subject = $subject::class;
        }

        parent::__construct($subject . PHP_EOL . json_encode($messages, JSON_PRETTY_PRINT));
    }
}
