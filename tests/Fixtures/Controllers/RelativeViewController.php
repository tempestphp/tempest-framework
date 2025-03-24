<?php

namespace Tests\Tempest\Fixtures\Controllers;

use Tempest\Router\Get;
use Tempest\View\GenericView;
use Tempest\View\View;

use function Tempest\view;

final class RelativeViewController
{
    #[Get('/relative-view-controller-with-view-function')]
    public function asFunction(): View
    {
        return view('./relative-view.view.php');
    }

    #[Get('/relative-view-controller-with-view-object')]
    public function asObject(): View
    {
        return new GenericView('./relative-view.view.php');
    }
}
