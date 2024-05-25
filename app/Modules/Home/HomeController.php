<?php

declare(strict_types=1);

namespace App\Modules\Home;

use Tempest\Http\Get;
use Tempest\Http\Request;
use Tempest\Log\Logger;
use Tempest\View\View;
use function Tempest\ll;

final readonly class HomeController
{
    public function __construct(private Logger $logger) {}

    #[Get(uri: '/')]
    public function __invoke(Request $request): View
    {
        ll('ll');
        $this->logger->debug('hi');
        $view = new HomeView(
            name: 'Brent',
        );

        //        lw($view);
        return $view;
    }
}
