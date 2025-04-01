<?php

namespace Tests\Tempest\Fixtures\Views;

use Tempest\View\View;
use Tempest\View\ViewProcessor;

final readonly class TestViewProcessor implements ViewProcessor
{
    public function process(View $view): View
    {
        return $view->data(global: 'test');
    }
}
