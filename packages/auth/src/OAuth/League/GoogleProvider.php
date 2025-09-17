<?php

declare(strict_types=1);

namespace Tempest\Auth\OAuth\League;

final class GoogleProvider
{
    use IsOauthProvider;

    public function configure(
        string $clientId,
        string $clientSecret,
        string $redirectUri,
        ?array $defaultScopes = null,
        string $stateSessionSlug = 'oauth-state'
    ): self
    {
        $this->defaultScopes = $defaultScopes ??= ['email', 'profile'];
        $this->authorizeEndpoint = 'https://accounts.google.com/o/oauth2/v2/auth';
        $this->accessTokenEndpoint = 'https://oauth2.googleapis.com/token';
        $this->userDataEndpoint = 'https://openidconnect.googleapis.com/v1/userinfo';

        return $this->configureInternalProvider(
            clientId: $clientId,
            clientSecret: $clientSecret,
            defaultScopes: $defaultScopes,
            redirectUri: $redirectUri,
            authorizeEndpoint: $this->authorizeEndpoint,
            accessTokenEndpoint: $this->accessTokenEndpoint,
            userDataEndpoint: $this->userDataEndpoint,
            stateSessionSlug: $stateSessionSlug
        );
    }
}