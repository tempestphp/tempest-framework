<?php

namespace Tempest\Auth\Exceptions;

use Exception;

final class NoPolicyWereFoundForResource extends Exception implements AuthenticationException
{
    public function __construct(
        public readonly string|object $resource,
    ) {
        parent::__construct(sprintf('No policies were found for resource `%s`.', is_object($resource) ? get_class($resource) : $resource));
    }
}
