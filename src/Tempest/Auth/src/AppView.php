<?php

namespace Tempest\Auth;

use Tempest\View\IsView;
use Tempest\View\View;

final class AppView implements View
{
    use IsView;

    public function __construct()
    {
        $this->path = __DIR__ . '/app-view.view.php';
    }
}
