<?php

declare(strict_types=1);

namespace App\Views;

use Tempest\View\IsView;
use Tempest\View\View;

final class ViewModelWithResponseData implements View
{
    use IsView;

    public function __construct()
    {
        $this->path('Views/base.php');
    }
}
