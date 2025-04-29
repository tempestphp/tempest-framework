<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Controllers;

use Tempest\Http\Response;
use Tempest\Http\Responses\Download;
use Tempest\Http\Responses\File;
use Tempest\Router\Get;

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
