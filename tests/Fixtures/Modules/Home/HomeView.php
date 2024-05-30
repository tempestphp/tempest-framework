<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Modules\Home;

use Tempest\View\IsView;
use Tempest\View\View;

final class HomeView implements View
{
    use IsView;

    public function __construct(
        public readonly string $name,
    ) {
        $this
            ->path('Modules/Home/home.php')
            ->extends('Views/base.php', title: 'Home');
    }
}
