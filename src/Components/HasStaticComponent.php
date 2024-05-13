<?php

declare(strict_types=1);

namespace Tempest\Console\Components;

interface HasStaticComponent
{
    public function getStaticComponent(): StaticComponent;
}
