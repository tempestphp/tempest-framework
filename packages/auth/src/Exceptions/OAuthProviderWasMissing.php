<?php

declare(strict_types=1);

namespace Tempest\Auth\Exceptions;

use AdamPaterson\OAuth2\Client\Provider\Slack;
use Exception;
use League\OAuth2\Client\Provider\Apple;
use League\OAuth2\Client\Provider\Facebook;
use League\OAuth2\Client\Provider\Instagram;
use League\OAuth2\Client\Provider\LinkedIn;
use Stevenmaguire\OAuth2\Client\Provider\Microsoft;
use Wohali\OAuth2\Client\Provider\Discord;

final class OAuthProviderWasMissing extends Exception implements AuthenticationException
{
    public function __construct(
        private readonly string $missing,
    ) {
        $packageName = $this->getPackageName();
        $message = $packageName
            ? sprintf('The `%s` OAuth provider is missing. Install it using `composer require %s`.', $missing, $packageName)
            : sprintf('The `%s` OAuth provider is missing.', $missing);

        parent::__construct($message);
    }

    private function getPackageName(): ?string
    {
        return match ($this->missing) {
            Facebook::class => 'league/oauth2-facebook',
            Instagram::class => 'league/oauth2-instagram',
            LinkedIn::class => 'league/oauth2-linkedin',
            Apple::class => 'patrickbussmann/oauth2-apple',
            Microsoft::class => 'stevenmaguire/oauth2-microsoft',
            Discord::class => 'wohali/oauth2-discord-new',
            Slack::class => 'adam-paterson/oauth2-slack',
            default => null,
        };
    }
}
