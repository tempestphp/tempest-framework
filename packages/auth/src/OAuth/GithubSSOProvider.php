<?php

declare(strict_types=1);

namespace Tempest\Auth\OAuth;

final class GithubSSOProvider implements OAuth2ProviderContract
{
    use IsOAuth2Provider;

    public private(set) array $scopes = ['user:email'];

    public private(set) string $authorizationUrl = 'https://github.com/login/oauth/authorize';

    public private(set) string $accessTokenUrl = 'https://github.com/login/oauth/access_token';

    public private(set) string $userDataUrl = 'https://api.github.com/user';

    public function __construct(
        public readonly string $clientId,
        public readonly string $clientSecret,
    ) {}
}