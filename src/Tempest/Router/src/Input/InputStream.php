<?php

namespace Tempest\Router\Input;

interface InputStream
{
    public function parse(): array;
}
