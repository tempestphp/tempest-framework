<?php

declare(strict_types=1);

namespace Tempest\Console;

interface Component
{
    public function render(): string;
}
