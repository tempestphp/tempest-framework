<?php

declare(strict_types=1);

namespace Tempest\Validation\Exceptions;

use Exception;
use Tempest\Support\ArrayHelper;
use Tempest\Support\LanguageHelper;
use Tempest\Validation\Rule;

final class ValidationException extends Exception
{
    public function __construct(object $object, public readonly array $failingRules)
    {
        $messages = [];

        foreach ($this->failingRules as $field => $failingRulesForField) {
            /** @var Rule $failingRuleForField */
            foreach ($failingRulesForField as $failingRuleForField) {
                $messages[$field][] = LanguageHelper::join(ArrayHelper::wrap($failingRuleForField->message()));
            }
        }

        parent::__construct($object::class . PHP_EOL . json_encode($messages, JSON_PRETTY_PRINT));
    }
}
