<?php

declare(strict_types=1);

namespace Tempest\Auth\OAuth\Config;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Facebook;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Tempest\Auth\OAuth\OAuthConfig;
use Tempest\Auth\OAuth\OAuthUser;
use Tempest\Mapper\ObjectFactory;
use UnitEnum;

final class FacebookOAuthConfig implements OAuthConfig
{
    public string $provider = Facebook::class;

    public function __construct(
        /**
         * The client ID for the Facebook OAuth application.
         */
        public string $clientId,

        /**
         * The client secret for the Facebook OAuth application.
         */
        public string $clientSecret,

        /**
         * The controller action to redirect to after the user authorizes the application.
         */
        public string|array $redirectTo,

        /**
         * The scopes to request from Facebook.
         *
         * @var string[]
         */
        public array $scopes = ['email', 'public_profile'],

        /**
         * The Graph API version to use.
         */
        public string $graphApiVersion = 'v18.0',

        /**
         * Identifier for this OAuth configuration.
         */
        public null|string|UnitEnum $tag = null,
    ) {}

    public function createProvider(): AbstractProvider
    {
        return new Facebook([
            'clientId' => $this->clientId,
            'clientSecret' => $this->clientSecret,
            'graphApiVersion' => $this->graphApiVersion,
        ]);
    }

    public function mapUser(ObjectFactory $factory, ResourceOwnerInterface $resourceOwner): OAuthUser
    {
        return $factory
            ->withData([
                'provider' => 'facebook',
                'raw' => $data = $resourceOwner->toArray(),
                ...$data,
            ])
            ->to(OAuthUser::class);
    }
}
