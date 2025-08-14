<?php

declare(strict_types=1);

namespace Tempest\Auth\OAuth;

use Tempest\Auth\OAuth\DataObjects\OAuthUserData;

interface OAuth2Provider
{
    /**
     * @var array<string> The default scopes for the OAuth2 provider.
     */
    public array $scopes {
        get;
    }

    /**
     * @var string The URL to redirect the user for authorization.
     */
    public string $authorizationUrl {
        get;
    }

    /**
     * @var string The URL to exchange the authorization code for an access token.
     */
    public string $accessTokenUrl {
        get;
    }

    /**
     * @var string The URL to fetch user data after authorization.
     */
    public string $userDataUrl {
        get;
    }

    /**
     * Return the query string parameters required for authorization endpoint.
     *
     * @return array<string, mixed>
     */
    public function getAuthorizationParameters(): array;

    /**
     * Return headers used in access token endpoint
     *
     * @param string $code The code verifier from OAuth redirection
     *
     * @return array<string, mixed>
     */
    public function getAccessTokenHeaders(string $code): array;

    /**
     * Return body fields used in access token endpoint
     *
     * @param string $code The code verifier from OAuth redirection
     *
     * @return array<string, mixed>
     */
    public function getAccessTokenFields(string $code): array;

    /**
     * Transform the response body into valid user data object
     *
     * @param array $body Json decoded response body from the user data endpoint
     */
    public function getUserDataFromResponse(array $body): OAuthUserData;
}
