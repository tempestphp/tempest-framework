<?php

final class FullyQualifiedViewCall
{
    public function __invoke(string $template)
    {
        return \Tempest\view($template);
    }
}
