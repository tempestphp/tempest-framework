<?php

declare(strict_types=1);

namespace Tempest\Auth\OAuth\Config;

use AdamPaterson\OAuth2\Client\Provider\Slack;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Tempest\Auth\OAuth\OAuthConfig;
use Tempest\Auth\OAuth\OAuthUser;
use Tempest\Mapper\ObjectFactory;
use UnitEnum;

final class SlackOAuthConfig implements OAuthConfig
{
    public string $provider = Slack::class;

    public function __construct(
        /**
         * The client ID for the Slack OAuth application.
         */
        public string $clientId,

        /**
         * The client secret for the Slack OAuth application.
         */
        public string $clientSecret,

        /**
         * The redirect URI for the OAuth flow.
         */
        public string|array $redirectUri,

        /**
         * The scopes to request from Slack.
         *
         * @var string[]
         */
        public array $scopes = ['identity.basic', 'identity.email'],

        /**
         * Identifier for this OAuth configuration.
         */
        public null|string|UnitEnum $tag = null,
    ) {}

    public function createProvider(): AbstractProvider
    {
        return new Slack([
            'clientId' => $this->clientId,
            'clientSecret' => $this->clientSecret,
        ]);
    }

    public function mapUser(ObjectFactory $factory, ResourceOwnerInterface $resourceOwner): OAuthUser
    {
        return $factory
            ->withData([
                'provider' => 'slack',
                'raw' => $data = $resourceOwner->toArray(),
                ...$data,
            ])
            ->to(OAuthUser::class);
    }
}
