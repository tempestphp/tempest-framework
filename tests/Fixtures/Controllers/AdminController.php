<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Controllers;

use Tempest\Auth\Allow;
use Tempest\Http\Response;
use Tempest\Http\Responses\Ok;
use Tempest\Router\Get;
use Tests\Tempest\Integration\Auth\Fixtures\CustomAuthorizer;
use Tests\Tempest\Integration\Auth\Fixtures\UserPermissionUnitEnum;

final readonly class AdminController
{
    #[Get('/admin')]
    #[Allow(UserPermissionUnitEnum::ADMIN)]
    public function admin(): Response
    {
        return new Ok();
    }

    #[Get('/guest')]
    #[Allow(UserPermissionUnitEnum::GUEST)]
    public function guest(): Response
    {
        return new Ok();
    }

    #[Get('/custom-authorizer')]
    #[Allow(CustomAuthorizer::class)]
    public function customAuthorizer(): Response
    {
        return new Ok();
    }
}
