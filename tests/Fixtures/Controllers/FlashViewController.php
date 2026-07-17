<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Controllers;

use Tempest\Http\Responses\Redirect;
use Tempest\Http\Session\Session;
use Tempest\Router\Get;
use Tempest\Router\Post;
use Tempest\View\View;

use function Tempest\View\view;

final readonly class FlashViewController
{
    public function __construct(
        private Session $session,
    ) {}

    #[Post('/flash-view')]
    public function flash(): Redirect
    {
        $this->session->flash('message', 'flashed-message');

        return new Redirect('/flash-view');
    }

    #[Get('/flash-view')]
    public function show(): View
    {
        return view('./flash-view.view.php');
    }
}
