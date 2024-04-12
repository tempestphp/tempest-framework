<?php

declare(strict_types=1);

namespace Tempest\Console;

interface HasCursor
{
    public function placeCursor(Cursor $cursor): void;
}
