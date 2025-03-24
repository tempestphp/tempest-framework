<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Controllers;

use Tempest\Router\Get;
use Tempest\Router\Response;
use Tempest\Router\Responses\Ok;

use function Tempest\defer;

final class DeferController
{
    public static bool $executed = false;

    #[Get('/defer')]
    public function __invoke(): Response
    {
        defer(function (): void {
            //            ll('defer start');
            //            sleep(2);
            //            ll('defer done');
            self::$executed = true;
        });

        return new Ok('ok');
    }
}
