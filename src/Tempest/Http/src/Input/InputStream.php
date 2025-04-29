<?php

declare(strict_types=1);

namespace Tempest\Http\Input;

interface InputStream
{
    public function parse(): array;
}
