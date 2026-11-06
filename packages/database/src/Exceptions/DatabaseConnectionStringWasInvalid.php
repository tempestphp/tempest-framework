<?php

declare(strict_types=1);

namespace Tempest\Database\Exceptions;

use Exception;

final class DatabaseConnectionStringWasInvalid extends Exception implements DatabaseException
{
    public static function couldNotBeParsed(): self
    {
        return new self('The database connection string could not be parsed. Make sure it is a valid URI with a scheme, e.g. `mysql://user:password@host:3306/database`.');
    }

    public static function hostWasMissing(): self
    {
        return new self('The database connection string must contain a host, or a Unix socket in its query parameters.');
    }

    public static function schemeWasNotSupported(string $scheme, array $supported): self
    {
        return new self(sprintf(
            'The database connection string scheme `%s` is not supported by this configuration. Supported schemes are: `%s`.',
            $scheme,
            implode('`, `', $supported),
        ));
    }
}
