<?php

declare(strict_types=1);

namespace Tempest\Filesystem\Local;

use Tempest\Filesystem\Stream;

final class LocalStream implements Stream
{
    /**
     * @var resource
     */
    private $fileHandle;

    public function __construct(private readonly string $path)
    {
    }

    public function open(): void
    {
        $this->fileHandle = fopen($this->path, 'r');
    }

    public function read(int $count): string
    {
        error_clear_last();

        $content = @fread($this->fileHandle, $count);

        if ($content === false) {
            // TODO: Throw an exception.
        }

        return $content;
    }

    public function write(string $data): int
    {
        error_clear_last();

        $bytesWritten = @fwrite($this->fileHandle, $data);

        if ($bytesWritten === false) {
            // TODO: Throw an exception.
        }

        return $bytesWritten;
    }

    public function close(): void
    {
        fclose($this->fileHandle);
    }
}
