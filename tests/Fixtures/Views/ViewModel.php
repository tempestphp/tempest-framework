<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Views;

use Tempest\View\IsView;
use Tempest\View\View;

final class ViewModel implements View
{
    use IsView;

    public function __construct(
        public readonly string $name,
    ) {
        $this->path = 'Views/withViewModel.php';
    }

    public function currentDate(): string
    {
        return '2020-01-01';
    }
}
