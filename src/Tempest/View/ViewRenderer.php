<?php

namespace Tempest\View;

interface ViewRenderer
{
    public function render(string|View|null $view): string;
}