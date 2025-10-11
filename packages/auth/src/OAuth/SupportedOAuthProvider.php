<?php

namespace Tempest\Auth\OAuth;

use AdamPaterson\OAuth2\Client\Provider\Slack;
use League\OAuth2\Client\Provider\Apple;
use League\OAuth2\Client\Provider\Facebook;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Provider\Github;
use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Provider\Instagram;
use League\OAuth2\Client\Provider\LinkedIn;
use Stevenmaguire\OAuth2\Client\Provider\Microsoft;
use Wohali\OAuth2\Client\Provider\Discord;

enum SupportedOAuthProvider: string
{
    case APPLE = Apple::class;
    case DISCORD = Discord::class;
    case FACEBOOK = Facebook::class;
    case GENERIC = GenericProvider::class;
    case GITHUB = Github::class;
    case GOOGLE = Google::class;
    case INSTAGRAM = Instagram::class;
    case LINKEDIN = LinkedIn::class;
    case MICROSOFT = Microsoft::class;
    case SLACK = Slack::class;

    public function composerPackage(): ?string
    {
        return match ($this) {
            self::APPLE => 'patrickbussmann/oauth2-apple',
            self::DISCORD => 'wohali/oauth2-discord-new',
            self::FACEBOOK => 'league/oauth2-facebook',
            self::GENERIC => null,
            self::GITHUB => 'league/oauth2-github',
            self::GOOGLE => 'league/oauth2-google',
            self::INSTAGRAM => 'league/oauth2-instagram',
            self::LINKEDIN => 'league/oauth2-linkedin',
            self::MICROSOFT => 'stevenmaguire/oauth2-microsoft',
            self::SLACK => 'adam-paterson/oauth2-slack',
        };
    }
}
