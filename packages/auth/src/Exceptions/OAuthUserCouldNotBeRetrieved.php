<?php

declare(strict_types=1);

namespace Tempest\Auth\Exceptions;

use Exception;
use Throwable;

final class OAuthUserCouldNotBeRetrieved extends Exception implements AuthenticationException
{
    public static function fromProviderException(?Throwable $previous = null): self
    {
        return new self(
            sprintf('Failed to get resource owner. %s', $previous?->getMessage()),
            previous: $previous,
        );
    }
}
