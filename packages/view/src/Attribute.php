<?php

declare(strict_types=1);

namespace Tempest\View;

interface Attribute
{
    public function apply(Element $element): ?Element;
}
