<?php

declare(strict_types=1);

namespace Tempest\Validation\Exceptions;

use Exception;

final class ValidationException extends Exception
{
    public function __construct(object $object, array $failingRules)
    {
        $messages = [];

        foreach ($failingRules as $field => $failingRulesForField) {
            /** @var \Tempest\Interface\Rule $failingRuleForField */
            foreach ($failingRulesForField as $failingRuleForField) {
                $messages[$field][] = $failingRuleForField->message();
            }
        }

        parent::__construct($object::class . PHP_EOL . json_encode($messages, JSON_PRETTY_PRINT));
    }
}
