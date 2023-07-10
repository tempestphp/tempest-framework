<?php

namespace App\Modules\Home;

use Tempest\Interfaces\View;
use Tempest\View\BaseView;

final class HomeView implements View
{
    use BaseView;

    public function __construct(
        public string $name,
    ) {
        $this->path = 'Modules/Home/home.php';
    }
}