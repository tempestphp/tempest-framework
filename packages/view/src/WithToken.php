<?php

declare(strict_types=1);

namespace Tempest\View;

use Tempest\View\Parser\Token;

interface WithToken
{
    public Token $token {
        get;
    }
}
