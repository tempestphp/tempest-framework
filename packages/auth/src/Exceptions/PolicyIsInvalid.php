<?php

namespace Tempest\Auth\Exceptions;

use Exception;

final class PolicyIsInvalid extends Exception implements AuthenticationException
{
    public static function resourceCouldNotBeInferred(string $policyName): self
    {
        return new self(sprintf(
            "The resource for policy `%s` could not be inferred because it is missing from the method's parameters. You must specify it in the attribute instead.",
            $policyName,
        ));
    }
}
