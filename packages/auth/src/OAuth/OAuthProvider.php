<?php

declare(strict_types=1);

namespace Tempest\Auth\OAuth;

use Tempest\Auth\OAuth\DataObjects\AccessToken;
use Tempest\Auth\OAuth\DataObjects\OAuthUserData;

interface OAuthProvider
{
    /**
     * @var array<string> The default scopes for the OAuth2 provider.
     */
    public array $defaultScopes {
        get;
    }

    /**
     * @var string The URL to redirect the user for authorization.
     */
    public string $authorizeEndpoint {
        get;
    }

    /**
     * @var string The URL to exchange the authorization code for an access token.
     */
    public string $accessTokenEndpoint {
        get;
    }

    /**
     * @var string The URL to fetch user data after authorization.
     */
    public string $userDataEndpoint {
        get;
    }

    /**
     * @param array<string, mixed>|null $additionalParameters Additional parameters to include in the authorization URL.
     * @param array<string>|null $scopes Scopes to request. If null, the default scopes will be used.
     * @param string|null $state A state parameter to include in the authorization URL. If null, a random state will be generated.
     */
    public function generateAuthorizationUrl(
        array $additionalParameters = [],
        ?array $scopes = null,
        ?string $state = null,
        ?string $scopeSeparator = ' ',
    ): string;

    /**
     * @param array<string, mixed>|null $additionalParameters Additional parameters to include in the request.
     */
    public function generateAccessToken(
        string $code,
        array $additionalParameters = [],
    ): AccessToken;

    /*
     * Fetches user data from the OAuth2 provider using the provided access token.
     */
    public function fetchUserDataFromToken(
        AccessToken $accessToken,
    ): OAuthUserData;
}
