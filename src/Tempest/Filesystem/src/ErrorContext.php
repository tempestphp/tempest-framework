<?php

declare(strict_types=1);

namespace Tempest\Filesystem;

final class ErrorContext
{
    private array $lastError = [];

    public function __construct()
    {
        error_clear_last();
    }

    public static function reset(): self
    {
        return new self();
    }

    public function getMessage(?string $default = 'Unknown error.'): string
    {
        return $this->getLastError()['message'] ?? $default;
    }

    public function commit(): self
    {
        $this->lastError = error_get_last() ?? [];

        return $this;
    }

    private function getLastError(): array
    {
        return $this->lastError ??= $this->commit()->lastError;
    }
}
