<?php

declare(strict_types=1);

namespace Tempest\Auth\OAuth\Providers;

use Tempest\Auth\OAuth\DataObjects\OAuthUserData;
use Tempest\Auth\OAuth\IsOAuth2Provider;
use Tempest\Auth\OAuth\OAuth2ProviderContract;

final class GoogleOAuthProvider implements OAuth2ProviderContract
{
    use IsOAuth2Provider {
        getAuthorizationParameters as parentGetAuthorizationParameters;
    }

    public private(set) array $scopes = ['email', 'profile'];

    public private(set) string $authorizationUrl = 'https://accounts.google.com/o/oauth2/v2/auth';

    public private(set) string $accessTokenUrl = 'https://oauth2.googleapis.com/token';

    public private(set) string $userDataUrl = 'https://openidconnect.googleapis.com/v1/userinfo';

    /**
     * @inheritDoc
     */
    public function getAuthorizationParameters(): array
    {
        return array_merge(
            $this->parentGetAuthorizationParameters(),
            [
                'response_type' => 'code',
                'redirect_uri' => $this->redirectUri
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function getUserDataFromResponse(array $body): OAuthUserData
    {
        return new OAuthUserData(
            id: $body['sub'],
            nickname: $body['given_name'],
            name: $body['name'],
            email: $body['email'],
            avatar: $body['picture'] ?? '',
        );
    }
}
