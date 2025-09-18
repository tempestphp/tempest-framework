<?php

declare(strict_types=1);

namespace Tempest\Auth\OAuth\Providers;

use Tempest\Auth\OAuth\IsOauthProvider;

class GenericProvider
{
    use IsOauthProvider;

    /**
     * @param array<string> $defaultScopes
     */
    public function configure(
        string $clientId,
        string $clientSecret,
        string $redirectUri,
        array $defaultScopes,
        string $authorizeEndpoint,
        string $accessTokenEndpoint,
        string $userDataEndpoint,
        string $stateSessionSlug = 'oauth-state',
    ): self {
        return $this->configureInternalProvider(
            clientId: $clientId,
            clientSecret: $clientSecret,
            redirectUri: $redirectUri,
            defaultScopes: $defaultScopes,
            authorizeEndpoint: $authorizeEndpoint,
            accessTokenEndpoint: $accessTokenEndpoint,
            userDataEndpoint: $userDataEndpoint,
            stateSessionSlug: $stateSessionSlug,
        );
    }
}
