<?php

declare(strict_types=1);

namespace Tempest\Auth;

interface Authorizer
{
    public function authorize(CanAuthorize $user): bool;
}
