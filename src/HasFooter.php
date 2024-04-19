<?php

declare(strict_types=1);

namespace Tempest\Console;

interface HasFooter
{
    public function renderFooter(): string;
}
