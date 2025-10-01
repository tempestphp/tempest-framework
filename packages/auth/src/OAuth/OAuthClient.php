<?php

declare(strict_types=1);

namespace Tempest\Auth\OAuth;

use League\OAuth2\Client\Token\AccessToken;

interface OAuthClient
{
    /**
     * Gets the authorization URL for the OAuth provider.
     */
    public function getAuthorizationUrl(array $scopes = [], array $options = []): string;

    /**
     * Gets the state parameter for CSRF protection.
     */
    public function getState(): ?string;

    /**
     * Exchanges an authorization code for an access token.
     */
    public function getAccessToken(string $code): AccessToken;

    /**
     * Gets user information from an OAuth provider using an access token.
     */
    public function getUser(AccessToken $token): OAuthUser;

    /**
     * Completes OAuth flow with code and get user information.
     */
    public function fetchUser(string $code): OAuthUser;
}
