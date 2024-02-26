<?php

declare(strict_types=1);

namespace Tempest\Exceptions;

use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Symfony\Component\VarDumper\VarDumper;
use Throwable;

final readonly class HttpExceptionHandler implements ExceptionHandler
{
    public function handle(Throwable $throwable): void
    {
        $varCloner = new VarCloner();
        $dumper = new HtmlDumper();

        $dumper->dump($varCloner->cloneVar($throwable), extraDisplayOptions: [
            'maxDepth' => 50,
            'maxStringLength' => 500,
        ]);
    }
}
