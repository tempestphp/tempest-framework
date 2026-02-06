<?php

use function Tempest\view;

final class ViewNamespaceChange
{
    public function __invoke(string $template)
    {
        return view($template);
    }
}
