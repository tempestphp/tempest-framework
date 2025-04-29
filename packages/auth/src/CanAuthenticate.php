<?php

declare(strict_types=1);

namespace Tempest\Auth;

use Tempest\Database\Id;

interface CanAuthenticate
{
    public ?Id $id {
        get;
    }
}
