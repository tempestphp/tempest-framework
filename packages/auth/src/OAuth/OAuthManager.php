<?php

declare(strict_types=1);

namespace Tempest\Auth\OAuth;

use Tempest\Container\Inject;
use Tempest\HttpClient\HttpClient;
use function Tempest\Support\str;

final class OAuthManager
{
    #[Inject]
    private HttpClient $httpClient;

    public function __construct(
        private readonly OAuth2ProviderContract $driver,
    ) {}

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
        }

        $queryString = http_build_query(array_filter($queryData), arg_separator: '&');

        return $this->driver->authorizationUrl . '?' . $queryString;
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