<?php

declare(strict_types=1);

namespace Tempest\Auth\OAuth\Config;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\LinkedIn;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Tempest\Auth\OAuth\OAuthConfig;
use Tempest\Auth\OAuth\OAuthUser;
use Tempest\Mapper\ObjectFactory;
use UnitEnum;

final class LinkedInOAuthConfig implements OAuthConfig
{
    public string $provider = LinkedIn::class;

    public function __construct(
        /**
         * The client ID for the LinkedIn OAuth application.
         */
        public string $clientId,

        /**
         * The client secret for the LinkedIn OAuth application.
         */
        public string $clientSecret,

        /**
         * The redirect URI for the OAuth flow.
         */
        public string|array $redirectUri,

        /**
         * The scopes to request from LinkedIn.
         *
         * @var string[]
         */
        public array $scopes = ['r_liteprofile', 'r_emailaddress'],

        /**
         * Identifier for this OAuth configuration.
         */
        public null|string|UnitEnum $tag = null,
    ) {}

    public function createProvider(): AbstractProvider
    {
        return new LinkedIn([
            'clientId' => $this->clientId,
            'clientSecret' => $this->clientSecret,
        ]);
    }

    public function mapUser(ObjectFactory $factory, ResourceOwnerInterface $resourceOwner): OAuthUser
    {
        return $factory
            ->withData([
                'provider' => 'linkedin',
                'raw' => $data = $resourceOwner->toArray(),
                ...$data,
            ])
            ->to(OAuthUser::class);
    }
}
