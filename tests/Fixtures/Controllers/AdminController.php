<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Controllers;

use Tempest\Auth\Allow;
use Tempest\Auth\Tests\Integration\Fixtures\CustomAuthorizer;
use Tempest\Auth\Tests\Integration\Fixtures\UserPermissionUnitEnum;
use Tempest\Http\Response;
use Tempest\Http\Responses\Ok;
use Tempest\Router\Get;

final readonly class AdminController
{
    #[Allow(UserPermissionUnitEnum::ADMIN)]
    #[Get('/admin')]
    public function admin(): Response
    {
        return new Ok();
    }

    #[Allow(UserPermissionUnitEnum::GUEST)]
    #[Get('/guest')]
    public function guest(): Response
    {
        return new Ok();
    }

    #[Allow(CustomAuthorizer::class)]
    #[Get('/custom-authorizer')]
    public function customAuthorizer(): Response
    {
        return new Ok();
    }
}
