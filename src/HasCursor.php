<?php

namespace Tempest\Console;

interface HasCursor
{
    public function getCursorPosition(): Point;
}