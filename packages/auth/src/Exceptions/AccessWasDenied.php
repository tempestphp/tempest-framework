<?php

namespace Tempest\Auth\Exceptions;

use Exception;
use Tempest\Auth\AccessControl\AccessDecision;

final class AccessWasDenied extends Exception implements AuthenticationException
{
    public function __construct(
        public readonly AccessDecision $accessDecision,
    ) {
        parent::__construct($accessDecision->message ?? 'Access was denied.');
    }
}
