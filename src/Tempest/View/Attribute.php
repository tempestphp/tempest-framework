<?php

namespace Tempest\View;

interface Attribute
{
    public function apply(Element $element): Element;
}