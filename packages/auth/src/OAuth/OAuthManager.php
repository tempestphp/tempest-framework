<?php

declare(strict_types=1);

namespace Tempest\Auth\OAuth;

use Tempest\Auth\OAuth\DataObjects\AccessToken;
use Tempest\Auth\OAuth\DataObjects\OAuthUserData;
use Tempest\Http\Session\Session;
use Tempest\Http\Session\SessionManager;
use Tempest\HttpClient\HttpClient;

use function json_encode;
use function Tempest\get;
use function Tempest\Support\str;

final class OAuthManager
{
    public private(set) string $stateSessionSlug = 'oauth-state';

    public function __construct(
        private readonly OAuth2Provider $provider,
        private readonly HttpClient $httpClient,
        private readonly Session $session,
    ) {}

    public function generateAuthorizationUrl(
        ?array $parameters = null,
        ?array $scopes = null,
        bool $isStateless = false,
    ): string {
        $parameters ??= $this->provider->getAuthorizationParameters();
        $scopes ??= $this->provider->scopes;

        if (! $isStateless) {
            $state = $this->generateState();

            $parameters['state'] = $state;
            $this->session->flash($this->stateSessionSlug, $state);
        }

        $queryString = http_build_query(array_filter($parameters), arg_separator: '&');

        return $this->provider->authorizationUrl . '?' . $queryString;
    }

    public function generateAccessToken(
        string $code,
    ): AccessToken {
        $response = $this->httpClient->post(
            uri: $this->provider->accessTokenUrl,
            headers: $this->provider->getAccessTokenHeaders($code),
            body: json_encode($this->provider->getAccessTokenFields($code)),
        );

        try {
            $body = json_decode($response->body, associative: true);
            $accessToken = AccessToken::from($body);
        } catch (\Throwable $e) {
            $errorMessage = 'Failed to decode access token response.';

            if (isset($body['error'], $body['error_description'])) {
                $errorMessage .= sprintf(
                    ' Error: "%s". Description: "%s"',
                    $body['error'],
                    $body['error_description'],
                );
            }

            throw new \RuntimeException($errorMessage);
        }

        return $accessToken;
    }

    public function fetchUserDataFromToken(AccessToken $accessToken): OAuthUserData
    {
        $response = $this->httpClient->get(
            uri: $this->provider->userDataUrl,
            headers: [
                'Authorization' => $accessToken->tokenType . ' ' . $accessToken->accessToken,
                'Accept' => 'application/json',
            ],
        );

        try {
            $body = json_decode($response->body, associative: true);
            $userData = $this->provider->getUserDataFromResponse($body);
        } catch (\Error $e) {
            $errorMessage = 'Failed to get user data.';

            if (isset($body['error'], $body['error_description'])) {
                $errorMessage .= sprintf(
                    ' Error: "%s". Description: "%s"',
                    $body['error'],
                    $body['error_description'],
                );
            }

            throw new \RuntimeException($errorMessage);
        }

        return $userData;
    }

    private function generateState(): string
    {
        return str()->random(40)->toString();
    }
}
