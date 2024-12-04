<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Controllers;

use Tempest\Router\Get;
use Tempest\Router\Response;
use Tempest\Router\Responses\Download;
use Tempest\Router\Responses\File;

final readonly class FileController
{
    #[Get('/pdf')]
    public function pdf(): Response
    {
        return new File(__DIR__ . '/sample.pdf');
    }

    #[Get('/png')]
    public function png(): Response
    {
        return new File(__DIR__ . '/sample.png');
    }

    #[Get('/download')]
    public function download(): Response
    {
        return new Download(__DIR__ . '/sample.pdf');
    }
}
