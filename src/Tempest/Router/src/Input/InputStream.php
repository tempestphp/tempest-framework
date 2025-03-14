<?php

declare(strict_types=1);

namespace Tempest\Router\Input;

interface InputStream
{
    public function parse(): array;
}
