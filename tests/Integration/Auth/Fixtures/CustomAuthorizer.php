<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Auth\Fixtures;

use Tempest\Auth\Authorizer;
use Tempest\Auth\CanAuthorize;
use Tempest\Auth\Install\User;

final readonly class CustomAuthorizer implements Authorizer
{
    public function authorize(CanAuthorize $user): bool
    {
        if (! ($user instanceof User)) {
            return false;
        }

        return $user->name === 'test';
    }
}
