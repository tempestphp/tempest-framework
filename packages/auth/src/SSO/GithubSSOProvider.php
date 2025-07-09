<?php

declare(strict_types=1);

namespace Tempest\Auth\SSO;

final class GithubSSOProvider implements OAuth2ProviderContract
{
    use IsOAuth2Provider;

    public private(set) array $scopes = ['user:email'];

    public private(set) string $authorizationUrl = 'https://github.com/login/oauth/authorize';

    public private(set) string $accessTokenUrl = 'https://github.com/login/oauth/access_token';

    public function __construct(
        public readonly string $clientId,
        public readonly string $clientSecret,
//        public readonly string $redirectUri = '',
    ) {}


//    protected string $userDataUrl {
//        get => 'https://api.github.com/user';
//    }

    public function getAuthorizationUrl(): string
    {
        return 'https://github.com/login/oauth/authorize';
    }
}