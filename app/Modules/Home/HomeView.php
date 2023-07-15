<?php

declare(strict_types=1);

namespace App\Modules\Home;

use Tempest\Interfaces\View;
use Tempest\View\BaseView;

final class HomeView implements View
{
    use BaseView;

    public function __construct(
        public readonly string $name,
    ) {
        $this
            ->path('Modules/Home/home.php')
            ->extends('Views/base.php', title: 'Home');
    }
}
