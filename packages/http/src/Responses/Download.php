<?php

declare(strict_types=1);

namespace Tempest\Http\Responses;

use Tempest\Http\ContentType;
use Tempest\Http\IsResponse;
use Tempest\Http\Response;

final class Download implements Response
{
    use IsResponse;

    public function __construct(string $path, ?string $filename = null)
    {
        $filename ??= pathinfo($path, PATHINFO_BASENAME);

        $this->addHeader('Content-Disposition', "attachment; filename=\"{$filename}\"")
            ->setContentType(ContentType::fromPath($path))
            ->removeHeader('Transfer-Encoding');

        if ($filesize = filesize($path)) {
            $this->addHeader('Content-Length', "{$filesize}");
        }

        $this->body = $path;
    }
}
