<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Views;

use Tempest\View\IsView;
use Tempest\View\View;

final class ViewWithResponseData implements View
{
    use IsView;

    public function __construct()
    {
        $this->path = 'Views/base.php';
    }
}
