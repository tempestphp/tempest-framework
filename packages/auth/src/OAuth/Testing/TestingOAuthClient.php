<?php

declare(strict_types=1);

namespace Tempest\Auth\OAuth\Testing;

use League\OAuth2\Client\Token\AccessToken;
use PHPUnit\Framework\Assert;
use Tempest\Auth\OAuth\OAuthClient;
use Tempest\Auth\OAuth\OAuthUser;
use Tempest\Support\Random;
use Tempest\Support\Str;
use UnitEnum;

final class TestingOAuthClient implements OAuthClient
{
    private ?string $state = null;

    private ?string $baseUrl = null;

    private ?string $clientId = null;

    private ?string $redirectUri = null;

    private array $authorizationUrls = [];

    private array $accessTokens = [];

    private array $callbacks = [];

    public function __construct(
        private OAuthUser $user,
        private readonly null|UnitEnum|string $tag = null,
    ) {}

    public function getAuthorizationUrl(array $scopes = [], array $options = []): string
    {
        $this->state = Random\secure_string(16);

        $url = sprintf(
            '%s/oauth/authorize?redirect_uri=%s&client_id=%s&state=%s',
            $this->baseUrl,
            $this->redirectUri,
            $this->clientId,
            $this->state,
        );

        $this->authorizationUrls[] = [
            'url' => $url,
            'scopes' => $scopes,
            'options' => $options,
            'state' => $this->state,
        ];

        return $url;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function getAccessToken(string $code): AccessToken
    {
        $token = new AccessToken([
            'access_token' => 'fat-' . $code,
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ]);

        $this->accessTokens[] = [
            'code' => $code,
            'token' => $token,
        ];

        return $token;
    }

    public function getUser(AccessToken $token): OAuthUser
    {
        return $this->user;
    }

    public function fetchUser(string $code): OAuthUser
    {
        $token = $this->getAccessToken($code);
        $user = $this->getUser($token);

        $this->callbacks[] = [
            'code' => $code,
            'token' => $token,
            'user' => $user,
        ];

        return $user;
    }

    /**
     * Sets the OAuth client ID.
     */
    public function withClientId(string $clientId): static
    {
        $this->clientId = $clientId;

        return $this;
    }

    /**
     * Sets the base URL for the OAuth provider.
     */
    public function withBaseUrl(string $url): static
    {
        $this->baseUrl = Str\strip_end($url, suffix: '/');

        return $this;
    }

    /**
     * Sets the redirect URI.
     */
    public function withRedirectUri(string $url): static
    {
        $this->redirectUri = $url;

        return $this;
    }

    /**
     * Replaces the user returned by the OAuth flow.
     */
    public function withUser(OAuthUser $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Asserts that an authorization URL was generated with the specified scopes or options.
     */
    public function assertAuthorizationUrlGenerated(?array $scopes = null, ?array $options = null): void
    {
        Assert::assertNotEmpty($this->authorizationUrls, 'No authorization URL was generated.');

        if ($options !== null) {
            $lastUrl = end($this->authorizationUrls);

            Assert::assertEquals(
                expected: $options,
                actual: $lastUrl['options'],
                message: 'Authorization URL options do not match.',
            );
        }

        if ($scopes !== null) {
            $lastUrl = end($this->authorizationUrls);

            Assert::assertEquals(
                expected: $scopes,
                actual: $lastUrl['scopes'],
                message: 'Authorization URL scopes do not match.',
            );
        }
    }

    /**
     * Asserts that the OAuth user was fetched with the given code.
     */
    public function assertUserFetched(string $code): void
    {
        Assert::assertNotEmpty(
            actual: array_filter($this->callbacks, fn (array $callback) => $callback['code'] === $code),
            message: sprintf('Callback with code "%s" was not handled.', $code),
        );
    }

    /**
     * Asserts that an access token was retrieved with the specified code.
     */
    public function assertAccessTokenRetrieved(?string $code = null): void
    {
        Assert::assertNotEmpty($this->accessTokens, 'No access token was retrieved.');

        if ($code !== null) {
            Assert::assertNotEmpty(
                actual: array_filter($this->accessTokens, fn (array $token) => $token['code'] === $code),
                message: sprintf('No access token was retrieved for code "%s".', $code),
            );
        }
    }

    /**
     * Asserts that the OAuth state matches the expected value.
     */
    public function assertStateEquals(string $expectedState): void
    {
        Assert::assertEquals($expectedState, $this->state, message: 'OAuth state does not match.');
    }

    /**
     * Gets the number of authorization URLs generated.
     */
    public function getAuthorizationUrlCount(): int
    {
        return count($this->authorizationUrls);
    }

    /**
     * Get the number of access tokens retrieved.
     */
    public function getAccessTokenCount(): int
    {
        return count($this->accessTokens);
    }

    /**
     * Get the number of callbacks handled.
     */
    public function getCallbackCount(): int
    {
        return count($this->callbacks);
    }
}
