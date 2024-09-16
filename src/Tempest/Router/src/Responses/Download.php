<?php

declare(strict_types=1);

namespace Tempest\Router\Responses;

use Generator;
use Tempest\Router\ContentType;
use Tempest\Router\IsResponse;
use Tempest\Router\Response;

final class Download implements Response
{
    use IsResponse;

    public function __construct(string $path, ?string $filename = null)
    {
        $filename ??= pathinfo($path, PATHINFO_BASENAME);

        $this
            ->addHeader('Content-Disposition', "attachment; filename=\"{$filename}\"")
            ->setContentType(ContentType::fromPath($path))
            ->removeHeader('Transfer-Encoding');

        if ($filesize = filesize($path)) {
            $this->addHeader('Content-Length', "{$filesize}");
        }

        $this->body = $this->readFile($path);
    }

    private function readFile(string $path): Generator
    {
        $handle = fopen($path, 'r');

        while ($content = fread($handle, 1024)) {
            yield $content;
        }

        fclose($handle);
    }
}
