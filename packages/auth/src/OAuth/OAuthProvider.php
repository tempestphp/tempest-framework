<?php

declare(strict_types=1);

namespace Tempest\Auth\OAuth;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;

interface OAuthProvider
{
    /**
     * Gets the authorization URL for the OAuth provider.
     */
    public function getAuthorizationUrl(array $options = []): string;

    /**
     * Gets the state parameter for CSRF protection.
     */
    public function getState(): ?string;

    /**
     * Exchanges an authorization code for an access token.
     */
    public function getAccessToken(string $code): AccessToken;

    /**
     * Gets the resource owner information using an access token.
     */
    public function getResourceOwner(AccessToken $token): ResourceOwnerInterface;
}
