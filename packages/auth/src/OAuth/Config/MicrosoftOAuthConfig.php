<?php

declare(strict_types=1);

namespace Tempest\Auth\OAuth\Config;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Stevenmaguire\OAuth2\Client\Provider\Microsoft;
use Tempest\Auth\OAuth\OAuthConfig;
use Tempest\Auth\OAuth\OAuthUser;
use Tempest\Mapper\ObjectFactory;
use UnitEnum;

final class MicrosoftOAuthConfig implements OAuthConfig
{
    public string $provider = Microsoft::class;

    public function __construct(
        /**
         * The client ID for the Microsoft OAuth application.
         */
        public string $clientId,

        /**
         * The client secret for the Microsoft OAuth application.
         */
        public string $clientSecret,

        /**
         * The redirect URI for the OAuth flow.
         */
        public string|array $redirectUri,

        /**
         * The scopes to request from Microsoft.
         *
         * @var string[]
         */
        public array $scopes = ['user.read'],

        /**
         * Identifier for this OAuth configuration.
         */
        public null|string|UnitEnum $tag = null,
    ) {}

    public function createProvider(): AbstractProvider
    {
        return new Microsoft([
            'clientId' => $this->clientId,
            'clientSecret' => $this->clientSecret,
        ]);
    }

    public function mapUser(ObjectFactory $factory, ResourceOwnerInterface $resourceOwner): OAuthUser
    {
        return $factory
            ->withData([
                'provider' => 'microsoft',
                'raw' => $data = $resourceOwner->toArray(),
                ...$data,
            ])
            ->to(OAuthUser::class);
    }
}
