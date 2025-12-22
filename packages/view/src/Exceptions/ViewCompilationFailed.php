<?php

declare(strict_types=1);

namespace Tempest\View\Exceptions;

use Exception;
use Tempest\Core\ProvidesContext;
use Throwable;

final class ViewCompilationFailed extends Exception implements ProvidesContext
{
    public function __construct(
        private(set) string $path,
        private(set) string $content,
        Throwable $previous,
    ) {
        parent::__construct(
            message: sprintf('View could not be compiled: %s.', lcfirst($previous->getMessage())),
            previous: $previous,
        );
    }

    public function context(): array
    {
        return [
            'path' => $this->path,
        ];
    }
}
