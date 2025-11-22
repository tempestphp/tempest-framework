<?php

declare(strict_types=1);

namespace Tempest\Auth\Exceptions;

use Exception;
use Tempest\Auth\OAuth\SupportedOAuthProvider;

final class OAuthProviderWasMissing extends Exception implements AuthenticationException
{
    public function __construct(
        private readonly string $missing,
    ) {
        $packageName = $this->getPackageName();
        $message = $packageName
            ? sprintf('The `%s` OAuth provider is missing. Install it using `composer require %s`.', $missing, $packageName)
            : sprintf('The `%s` OAuth provider is missing.', $missing);

        parent::__construct($message);
    }

    private function getPackageName(): ?string
    {
        return SupportedOAuthProvider::tryFrom($this->missing)?->composerPackage();
    }
}
