<?php

declare(strict_types=1);

namespace Tempest\Auth\OAuth\League;

use Tempest\Auth\OAuth\DataObjects\OAuthUserData;

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

    /**
     * @inheritDoc
     */
    protected function createUserDataFromResponse(array $userData): OAuthUserData
    {
        return new OAuthUserData(
            id: $userData['sub'] ?? null,
            nickname: $userData['given_name'] ?? null,
            name: $userData['family_name'] ?? null,
            email: $userData['email'] ?? null,
            avatar: $userData['picture'] ?? null,
            rawData: $userData
        );
    }
}