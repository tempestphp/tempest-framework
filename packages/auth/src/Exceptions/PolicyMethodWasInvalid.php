<?php

namespace Tempest\Auth\Exceptions;

use Exception;

final class PolicyMethodWasInvalid extends Exception implements AuthenticationException
{
    public static function resourceParameterIsInvalid(string $policyName, string $expectedType): self
    {
        return new self(sprintf('The type of the resource parameter of the `%s` policy does not match the expected type `%s`.', $policyName, $expectedType));
    }

    public static function subjectParameterIsInvalid(string $policyName, string $expectedType): self
    {
        return new self(sprintf('The type of the subject parameter of the `%s` policy does not match the expected type `%s`.', $policyName, $expectedType));
    }
}
