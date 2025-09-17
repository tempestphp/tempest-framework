<?php

declare(strict_types=1);

namespace Tempest\Auth\OAuth\Providers;

use Tempest\Auth\OAuth\DataObjects\OAuthUserData;
use Tempest\Auth\OAuth\IsOauthProvider;
use Tempest\Auth\OAuth\OAuthProvider;

final class GithubProvider implements OAuthProvider
{
    use IsOauthProvider;

    public function configure(
        string $clientId,
        string $clientSecret,
        string $redirectUri,
        ?array $defaultScopes = null,
        string $stateSessionSlug = 'oauth-state',
    ): self {
        $this->defaultScopes = $defaultScopes ??= ['user:email'];
        $this->authorizeEndpoint = 'https://github.com/login/oauth/authorize';
        $this->accessTokenEndpoint = 'https://github.com/login/oauth/access_token';
        $this->userDataEndpoint = 'https://api.github.com/user';

        return $this->configureInternalProvider(
            clientId: $clientId,
            clientSecret: $clientSecret,
            defaultScopes: $defaultScopes,
            redirectUri: $redirectUri,
            authorizeEndpoint: $this->authorizeEndpoint,
            accessTokenEndpoint: $this->accessTokenEndpoint,
            userDataEndpoint: $this->userDataEndpoint,
            stateSessionSlug: $stateSessionSlug,
        );
    }

    /**
     * @inheritDoc
     */
    protected function createUserDataFromResponse(array $userData): OAuthUserData
    {
        return new OAuthUserData(
            id: $userData['id'] ?? null,
            nickname: $userData['login'] ?? null,
            name: $userData['name'] ?? null,
            email: $userData['email'] ?? null,
            avatar: $userData['avatar_url'] ?? null,
            rawData: $userData,
        );
    }
}
