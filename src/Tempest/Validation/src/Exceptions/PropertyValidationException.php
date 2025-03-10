<?php

namespace Tempest\Validation\Exceptions;

use Exception;
use Tempest\Reflection\PropertyReflector;
use Tempest\Validation\Rule;

use function Tempest\Support\arr;

final class PropertyValidationException extends Exception
{
    /**
     * @param Rule[] $failingRules
     */
    public function __construct(
        public readonly PropertyReflector $property,
        public readonly array $failingRules,
    ) {
        $messages = [];

        foreach ($this->failingRules as $failingRule) {
            $messages[] = arr($failingRule->message())->join()->toString();
        }

        parent::__construct($this->property->getClass()->getName() . '::' . $this->property->getName() . PHP_EOL . json_encode($messages, JSON_PRETTY_PRINT));

        parent::__construct($this->property->getName());
    }
}
