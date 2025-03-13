<?php

declare(strict_types=1);

namespace Tempest\Validation\Exceptions;

use Exception;
use Tempest\Validation\Rule;

use function Tempest\Support\arr;

final class ValidationException extends Exception
{
    public function __construct(
        public readonly object $object,
        public readonly array $failingRules,
    ) {
        $messages = [];

        foreach ($this->failingRules as $field => $failingRulesForField) {
            /** @var Rule $failingRuleForField */
            foreach ($failingRulesForField as $failingRuleForField) {
                $messages[$field][] = arr($failingRuleForField->message())->join()->toString();
            }
        }

        parent::__construct($object::class . PHP_EOL . json_encode($messages, JSON_PRETTY_PRINT));
    }
}
