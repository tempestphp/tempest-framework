<?php

declare(strict_types=1);

namespace Tempest\Auth\OAuth;

use Tempest\Auth\OAuth\DataObjects\AccessToken;
use Tempest\Auth\OAuth\DataObjects\OAuthUserData;
use Tempest\Http\Session\Session;
use Tempest\HttpClient\HttpClient;
use function dd;
use function json_encode;
use function Tempest\get;
use function Tempest\Support\str;

final class OAuthManager
{
    private readonly HttpClient $httpClient;

    public function __construct(
        private readonly OAuth2ProviderContract $driver,
    ) {
        $this->httpClient = get(HttpClient::class);
    }

    public function generateAuthorizationUrl(
        ?array $scopes = null,
        bool $isStateless = false
    ): string {
        $scopes ??= $this->driver->scopes;
        $queryData = [
            'scope' => $this->formatScopes($scopes, $this->driver->scopeSeparator),
            'client_id' => $this->driver->clientId,
        ];

        if ( ! $isStateless ) {
            $queryData['state'] = $this->generateState();
            // TODO : Store the state in the session for later validation
        }

        $queryString = http_build_query(array_filter($queryData), arg_separator: '&');

        return $this->driver->authorizationUrl . '?' . $queryString;
    }

    public function generateAccessToken(
        string $code,
        ?string $state = null
    ): AccessToken {
        $response = $this->httpClient->post(
            uri: $this->driver->accessTokenUrl,
            headers: $this->driver->getAccessTokenHeaders($code),
            body: json_encode($this->driver->getAccessTokenFields($code))
        );

        try {
            $body = json_decode($response->body, associative: true);
            $accessToken = AccessToken::from($body);
        } catch ( \Error $e ) {
            $errorMessage = 'Failed to decode access token response.';

            if ( isset($body['error'], $body['error_description']) ) {
                $errorMessage .= sprintf(
                    ' Error: "%s". Description: "%s"',
                    $body['error'],
                    $body['error_description']
                );
            }

            throw new \RuntimeException( $errorMessage );
        }

        return $accessToken;
    }

    public function fetchUserDataFromToken(AccessToken $accessToken): OAuthUserData
    {
        $response = $this->httpClient->get(
            uri: $this->driver->userDataUrl,
            headers: [
                'Authorization' => $accessToken->tokenType . ' ' . $accessToken->accessToken,
                'Accept' => 'application/json',
            ]
        );

        try {
            $body = json_decode($response->body, associative: true);
            $userData = $this->driver->getUserDataFromResponse($body);
        } catch ( \Error $e ) {
            $errorMessage = 'Failed to get user data.';

            if ( isset($body['error'], $body['error_description']) ) {
                $errorMessage .= sprintf(
                    ' Error: "%s". Description: "%s"',
                    $body['error'],
                    $body['error_description']
                );
            }

            throw new \RuntimeException( $errorMessage );
        }

        return $userData;
    }

    private function formatScopes(array $scopes, string $scopeSeparator): string
    {
        return implode($scopeSeparator, $scopes);
    }

    private function generateState(): string
    {
        return str()->random(40)->toString();
    }
}