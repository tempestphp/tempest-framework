<?php

namespace Tempest\Icon;

use Exception;

final readonly class IconDownloadFailed
{
    public function __construct(
        public string $collection,
        public string $name,
        public Exception $exception,
    ) {}
}
