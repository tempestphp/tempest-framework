<?php

declare(strict_types=1);

namespace Tempest\Auth\Installer\oauth;

use Tempest\Auth\Authentication\Authenticatable;
use Tempest\Auth\OAuth\OAuthClient;
use Tempest\Auth\OAuth\OAuthUser;
use Tempest\Container\Tag;
use Tempest\Discovery\SkipDiscovery;
use Tempest\Http\Request;
use Tempest\Http\Responses\Redirect;
use Tempest\Router\Get;

use function Tempest\Database\query;

#[SkipDiscovery]
final readonly class OAuthControllerStub
{
    public function __construct(
        #[Tag('tag_name')]
        private OAuthClient $oauth,
    ) {}

    #[Get('redirect-route')]
    public function redirect(): Redirect
    {
        return $this->oauth->createRedirect();
    }

    #[Get('callback-route')]
    public function callback(Request $request): Redirect
    {
        // TODO: implement, the code below is an example

        $this->oauth->authenticate(
            request: $request,
            map: fn (OAuthUser $user): Authenticatable => query('user-model-fqcn')->updateOrCreate([
                'provider_db_column' => $user->id,
            ], [
                'provider_db_column' => $user->id,
                'username' => $user->nickname,
                'email' => $user->email,
            ]),
        );

        return new Redirect('/');
    }
}
