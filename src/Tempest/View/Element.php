<?php

namespace Tempest\View;

interface Element
{
    public function render(ViewRenderer $renderer): string;
}