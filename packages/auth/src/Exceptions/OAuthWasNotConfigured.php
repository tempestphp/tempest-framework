<?php

declare(strict_types=1);

namespace Tempest\Auth\Exceptions;

use Exception;
use Tempest\Support\Str;
use UnitEnum;

final class OAuthWasNotConfigured extends Exception implements AuthenticationException
{
    public static function configurationWasMissing(null|string|UnitEnum $tag): self
    {
        $tag = Str\parse($tag, default: null);

        return new self(
            $tag
                ? sprintf('No OAuth configuration was found for the "%s" tag.', $tag)
                : 'No OAuth configuration was found.',
        );
    }
}
