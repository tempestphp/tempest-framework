<?php

declare(strict_types=1);

namespace Tempest\Auth\OAuth\Providers;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

final class GoogleProvider extends AbstractProvider
{
    use BearerAuthorizationTrait;

    /**
     * @var array<string> Scopes that will be sent to the authorization server.
     * @link https://developers.google.com/identity/protocols/googlescopes
     */
    public array $scopes {
        get => $this->scopes;
        set(array $value) => $this->scopes = array_unique($value);
    }

    public private(set) array $defaultScopes = ['openid', 'email', 'profile'];

    public private(set) ?string $accessType;
    public private(set) ?string $hostedDomains;
    public private(set) ?string $prompt;

    /**
     * @inheritDoc
     */
    public function getBaseAuthorizationUrl(): string
    {
        return 'https://accounts.google.com/o/oauth2/v2/auth';
    }

    /**
     * @inheritDoc
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return 'https://oauth2.googleapis.com/token';
    }

    /**
     * @inheritDoc
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return 'https://openidconnect.googleapis.com/v1/userinfo';
    }

    /**
     * @inheritDoc
     */
    protected function getAuthorizationParameters(array $options): array
    {
        if ((empty($options['hd'] ?? null)) && ! empty($this->hostedDomains)) {
            $options['hd'] = $this->hostedDomains;
        }

        if ((empty($options['access_type'] ?? null)) && ! empty($this->accessType)) {
            $options['access_type'] = $this->accessType;
        }

        if ((empty($options['prompt'] ?? null)) && ! empty($this->prompt)) {
            $options['prompt'] = $this->prompt;
        }

        $scopes = $this->scopes;

        return parent::getAuthorizationParameters($options);
    }

    /**
     * @inheritDoc
     */
    protected function getDefaultScopes()
    {
        // TODO: Implement getDefaultScopes() method.
    }

    /**
     * @inheritDoc
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        // TODO: Implement checkResponse() method.
    }

    /**
     * @inheritDoc
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        // TODO: Implement createResourceOwner() method.
    }
}