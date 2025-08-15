<?php

declare(strict_types=1);

namespace Tempest\Auth;

use Tempest\Database\PrimaryKey;

interface CanAuthenticate
{
    public ?PrimaryKey $id {
        get;
    }
}
