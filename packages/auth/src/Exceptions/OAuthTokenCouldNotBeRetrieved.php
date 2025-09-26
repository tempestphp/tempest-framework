<?php

declare(strict_types=1);

namespace Tempest\Auth\Exceptions;

use Exception;
use Throwable;

final class OAuthTokenCouldNotBeRetrieved extends Exception implements AuthenticationException
{
    public static function fromProviderException(?Throwable $previous = null): self
    {
        return new self(
            message: sprintf('Failed to exchange code for access token. %s', $previous?->getMessage()),
            previous: $previous,
        );
    }
}
