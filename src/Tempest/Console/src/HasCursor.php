<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\Console\Terminal\Terminal;

interface HasCursor
{
    public function getCursorPosition(Terminal $terminal): Point;

    public function cursorVisible(): bool;
}
