<?php

declare(strict_types=1);

namespace Tempest\Auth\SSO;

use Tempest\Container\Inject;
use Tempest\HttpClient\HttpClient;
use function Tempest\Support\str;

final class SSOManager
{
    #[Inject]
    private HttpClient $httpClient;

    public function __construct(
        private readonly OAuthProviderContract $driver,
    ) {}

    public function redirect(
        array $scopes = [],
        bool $isStateless = false
    ) {
        $queryData = [
            'scope' => $this->formatScopes($scopes, $this->driver->scopeSeparator),
            'client_id' => $this->driver->clientId,
        ];

        if ( ! $isStateless ) {
            $queryData['state'] = $this->generateState();
        }

        $queryString = http_build_query(array_filter($queryData), arg_separator: '&');

        dd(
            'test',
            $this->driver->getAuthorizationUrl() . '?' . $queryString
//            $this->httpClient->get($this->driver->getAuthorizationUrl() . '?' . $queryString);
        );
    }

    private function formatScopes(array $scopes, $scopeSeparator): string
    {
        return implode($scopeSeparator, $scopes);
    }

    private function generateState(): string
    {
        return str()->random(40)->toString();
    }
}