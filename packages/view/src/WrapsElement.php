<?php

declare(strict_types=1);

namespace Tempest\View;

interface WrapsElement
{
    public function getWrappingElement(): Element;
}
