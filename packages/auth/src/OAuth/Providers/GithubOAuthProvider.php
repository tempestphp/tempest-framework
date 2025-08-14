<?php

declare(strict_types=1);

namespace Tempest\Auth\OAuth\Providers;

use Tempest\Auth\OAuth\DataObjects\OAuthUserData;
use Tempest\Auth\OAuth\IsOAuth2Provider;
use Tempest\Auth\OAuth\OAuth2Provider;

final class GithubOAuthProvider implements OAuth2Provider
{
    use IsOAuth2Provider;

    public private(set) array $scopes = ['user:email'];

    public private(set) string $authorizationUrl = 'https://github.com/login/oauth/authorize';

    public private(set) string $accessTokenUrl = 'https://github.com/login/oauth/access_token';

    public private(set) string $userDataUrl = 'https://api.github.com/user';

    /**
     * @inheritDoc
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
