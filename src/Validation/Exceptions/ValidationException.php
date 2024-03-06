<?php

declare(strict_types=1);

namespace Tempest\Validation\Exceptions;

use Exception;
use Tempest\Validation\Rule;

final class ValidationException extends Exception
{
    public readonly array $failingRules;

    public function __construct(object $object, array $failingRules)
    {
        $this->failingRules = $failingRules;

        $messages = [];

        foreach ($failingRules as $field => $failingRulesForField) {
            /** @var Rule $failingRuleForField */
            foreach ($failingRulesForField as $failingRuleForField) {
                $messages[$field][] = $failingRuleForField->message();
            }
        }

        parent::__construct($object::class . PHP_EOL . json_encode($messages, JSON_PRETTY_PRINT));
    }
}
