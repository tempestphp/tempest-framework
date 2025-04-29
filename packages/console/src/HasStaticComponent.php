<?php

declare(strict_types=1);

namespace Tempest\Console;

interface HasStaticComponent
{
    public StaticConsoleComponent $staticComponent {
        get;
    }
}
