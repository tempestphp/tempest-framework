<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Controllers;

use Tempest\Auth\HasPermission;
use Tempest\Http\Get;
use Tempest\Http\Response;
use Tempest\Http\Responses\Ok;
use Tests\Tempest\Integration\Auth\Fixtures\CustomAuthorizer;
use Tests\Tempest\Integration\Auth\Fixtures\UserPermissionUnitEnum;

final readonly class AdminController
{
    #[Get('/admin')]
    #[HasPermission(UserPermissionUnitEnum::ADMIN)]
    public function admin(): Response
    {
        return new Ok();
    }

    #[Get('/guest')]
    #[HasPermission(UserPermissionUnitEnum::GUEST)]
    public function guest(): Response
    {
        return new Ok();
    }

    #[Get('/custom-authorizer')]
    #[HasPermission(CustomAuthorizer::class)]
    public function customAuthorizer(): Response
    {
        return new Ok();
    }
}
