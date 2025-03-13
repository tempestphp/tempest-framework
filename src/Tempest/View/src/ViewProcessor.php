<?php

namespace Tempest\View;

interface ViewProcessor
{
    public function process(View $view): View;
}