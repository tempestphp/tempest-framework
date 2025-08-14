<?php

declare(strict_types=1);

namespace Tempest\Auth\OAuth;

trait IsOAuth2Provider
{
    public private(set) string $scopeSeparator = ' ';

    public function __construct(
        public readonly string $clientId,
        public readonly string $clientSecret,
        public readonly string $redirectUri
    ) {}

    /**
     * Return the query string parameters required for authorization endpoint.
     *
     * @return array<string, mixed>
     */
    public function getAuthorizationParameters(): array
    {
        return [
            'scope' => $this->formatScopes($this->scopes, $this->scopeSeparator),
            'client_id' => $this->clientId,
        ];
    }

    /**
     * Return headers used in access token endpoint
     *
     * @param string $code The code verifier from OAuth redirection
     *
     * @return array<string, mixed>
     */
    public function getAccessTokenHeaders(string $code): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Return body fields used in access token endpoint
     *
     * @param string $code The code verifier from OAuth redirection
     *
     * @return array<string, mixed>
     */
    public function getAccessTokenFields(string $code): array
    {
        return [
            'grant_type' => 'authorization_code',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'redirect_uri' => $this->redirectUri,
        ];
    }

    private function formatScopes(array $scopes, string $scopeSeparator): string
    {
        return implode($scopeSeparator, $scopes);
    }
}
