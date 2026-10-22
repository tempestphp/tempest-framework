<?php

declare(strict_types=1);

namespace Tempest\Database\Exceptions;

use Exception;

final class DatabaseUrlWasInvalid extends Exception implements DatabaseException
{
    public static function couldNotBeParsed(): self
    {
        return new self('The database URL could not be parsed. Make sure it contains at least a scheme and a host, e.g. `mysql://user:password@host:3306/database`.');
    }

    public static function schemeWasNotSupported(?string $scheme, array $supported): self
    {
        return new self(sprintf(
            'The database URL scheme `%s` is not supported by this configuration. Supported schemes are: `%s`.',
            $scheme ?? '',
            implode('`, `', $supported),
        ));
    }
}
