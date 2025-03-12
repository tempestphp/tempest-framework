<?php

declare(strict_types=1);

namespace Tempest\Validation\Exceptions;

use Exception;
use Tempest\Reflection\PropertyReflector;
use Tempest\Validation\Rule;

use function Tempest\Support\arr;

final class PropertyValidationException extends Exception
{
    /**
     * @param Rule[][] $failingRules
     */
    public function __construct(
        public readonly PropertyReflector $property,
        public readonly array $failingRules,
    ) {
        $messages = [];
lw($this->failingRules);
        foreach ($this->failingRules as $key => $failingRulesForProperty) {
            foreach ($failingRulesForProperty as $failingRule) {
                $messages[$key][] = arr($failingRule->message())->join()->toString();
            }
        }

        parent::__construct($this->property->getClass()->getName() . '::' . $this->property->getName() . PHP_EOL . json_encode($messages, JSON_PRETTY_PRINT));

        parent::__construct($this->property->getName());
    }
}
