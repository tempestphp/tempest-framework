<?php

declare(strict_types=1);

namespace Tempest\View;

interface ViewComponent extends Element
{
    public static function getName(): string;
}
