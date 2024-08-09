<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Modules\Home;

use Tempest\Http\Get;
use Tempest\Http\Request;
use Tempest\Log\Logger;
use Tempest\View\View;

final readonly class HomeController
{
    public function __construct(private Logger $logger)
    {
    }

    #[Get(uri: '/')]
    public function __invoke(Request $request): View
    {
        ll('ll');
        $this->logger->debug('logger');

        //        lw($view);
        return new HomeView(
            name: 'Brent',
        );
    }
}
