<?php

declare(strict_types=1);

namespace Tempest\Auth\OAuth;

use Tempest\Auth\OAuth\DataObjects\OAuthUserData;

final class GithubOAuthProvider implements OAuth2ProviderContract
{
    use IsOAuth2Provider;

    public private(set) array $scopes = ['user:email'];

    public private(set) string $authorizationUrl = 'https://github.com/login/oauth/authorize';

    public private(set) string $accessTokenUrl = 'https://github.com/login/oauth/access_token';

    public private(set) string $userDataUrl = 'https://api.github.com/user';

    // TODO : goto trait
    public function __construct(
        public readonly string $clientId,
        public readonly string $clientSecret,
    ) {}

    /**
     * TODO : goto trait
     * Return headers used in access token endpoint
     *
     * @param string $code The code verifier from OAuth redirection
     *
     * @return array<string, mixed>
     */
    public function getAccessTokenHeaders(string $code): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * TODO : goto trait
     * Return body fields used in access token endpoint
     *
     * @param string $code The code verifier from OAuth redirection
     *
     * @return array<string, mixed>
     */
    public function getAccessTokenFields(string $code): array
    {
        return [
            'grant_type' => 'authorization_code',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'redirect_uri' => 'http://127.0.0.1:8000/auth/github/callback',
        ];
    }

    /**
     * TODO : goto trait, maybe as abstract method
     */
    public function getUserDataFromResponse(array $body): OAuthUserData
    {
        return new OAuthUserData(
            id: $body['id'],
            nickname: $body['login'],
            name: $body['name'],
            email: $body['email'],
            avatar: $body['avatar_url'] ?? '',
        );
    }
}
