<?php

declare(strict_types=1);

namespace Tempest\Auth\SSO;

final class GithubSSOProvider implements OAuth2ProviderContract
{
    use IsOAuth2Provider;

    private(set) string $clientId = 'your-github-client-id';
    private(set) string $clientSecret = 'your-github-client-secret';
    private(set) array $scopes = ['user:email'];

    private(set) string $accessTokenUrl = 'https://github.com/login/oauth/access_token';

//    protected string $userDataUrl {
//        get => 'https://api.github.com/user';
//    }

    public function getAuthorizationUrl(): string
    {
        return 'https://github.com/login/oauth/authorize';
    }
}