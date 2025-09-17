<?php

declare(strict_types=1);

namespace Tempest\Auth\OAuth\League;

class OAuthGenericProvider
{
    use IsOauthProvider;

    /**
     * @param array<string> $defaultScopes
     */
    public function configure(
       string $clientId,
       string $clientSecret,
       array $defaultScopes,
       string $redirectUri,
       string $authorizeEndpoint,
       string $accessTokenEndpoint,
       string $userDataEndpoint,
       string $stateSessionSlug = 'oauth-state',
    ): self
    {
        return $this->configureInternalProvider(
            clientId: $clientId,
            clientSecret: $clientSecret,
            defaultScopes: $defaultScopes,
            redirectUri: $redirectUri,
            authorizeEndpoint: $authorizeEndpoint,
            accessTokenEndpoint: $accessTokenEndpoint,
            userDataEndpoint: $userDataEndpoint,
            stateSessionSlug: $stateSessionSlug
        );
    }
}