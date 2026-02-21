<?php

declare(strict_types=1);

namespace Tempest\Core;

interface ResetableStatic
{
    public static function resetStatic(): void;
}
