<?php

declare(strict_types=1);

namespace Tempest\View\Exceptions;

use Exception;
use Tempest\Core\ProvidesContext;
use Throwable;

final class ViewCompilationFailed extends Exception implements ProvidesContext
{
    public function __construct(
        private(set) readonly string $path,
        private(set) readonly string $content,
        Throwable $previous,
        private(set) readonly ?string $sourcePath = null,
        private(set) readonly ?int $sourceLine = null,
    ) {
        parent::__construct(
            message: sprintf($previous->getMessage()),
            previous: $previous,
        );

        $this->file = $this->sourcePath ?? $this->file;
        $this->line = $this->sourceLine ?? $this->line;
    }

    public function context(): array
    {
        return [
            'path' => $this->path,
            'sourcePath' => $this->sourcePath,
            'sourceLine' => $this->sourceLine,
        ];
    }
}
