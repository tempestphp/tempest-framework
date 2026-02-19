<?php

declare(strict_types=1);

namespace Tempest\Auth\OAuth;

use Closure;
use League\OAuth2\Client\Token\AccessToken;
use Tempest\Auth\Authentication\Authenticatable;
use Tempest\Http\Request;
use Tempest\Http\Responses\Redirect;

/**
 * @template T of Authenticatable
 */
interface OAuthClient
{
    /**
     * Gets the authorization URL for the OAuth provider.
     */
    public function buildAuthorizationUrl(array $scopes = [], array $options = []): string;

    /**
     * Creates a redirect response for the OAuth flow.
     */
    public function createRedirect(array $scopes = [], array $options = []): Redirect;

    /**
     * Gets the state parameter for CSRF protection.
     */
    public function getState(): ?string;

    /**
     * Exchanges an authorization code for an access token.
     */
    public function requestAccessToken(string $code): AccessToken;

    /**
     * Gets user information from an OAuth provider using an access token.
     */
    public function fetchUser(AccessToken $token): OAuthUser;

    /**
     * Authenticates a user based on the given OAuth callback request.
     *
     * @param Closure(OAuthUser): T $map A callback that should return an authenticatable model from the given OAuthUser. Typically, the callback is also responsible for saving the user to the database.
     */
    public function authenticate(Request $request, Closure $map): Authenticatable;
}
