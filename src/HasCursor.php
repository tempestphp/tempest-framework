<?php

namespace Tempest\Console;

use Tempest\Console\Terminal\Cursor;

interface HasCursor
{
    public function placeCursor(Cursor $cursor): void;
}