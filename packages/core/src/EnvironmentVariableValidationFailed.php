<?php

namespace Tempest\Core;

use Exception;
use Tempest\Validation\FailingRule;
use Tempest\Validation\Validator;

use function Tempest\Support\arr;

final class EnvironmentVariableValidationFailed extends Exception
{
    /**
     * @param FailingRule[] $failingRules
     */
    public function __construct(
        private(set) string $name,
        private(set) mixed $value,
        private(set) array $failingRules,
        private(set) Validator $validator,
    ) {
        parent::__construct(vsprintf("Environment variable [%s] is not valid:\n- %s", [
            $name,
            arr($failingRules)
                ->map(fn (FailingRule $failingRule) => $validator->getErrorMessage($failingRule, $name))
                ->implode("\n- ")
                ->toString(),
        ]));
    }
}
