<?php

declare(strict_types=1);

namespace Tempest\Filesystem\Driver\Local;

use Tempest\Filesystem\Exception\UnableToCloseStream;
use Tempest\Filesystem\Exception\UnableToOpenStream;
use Tempest\Filesystem\Stream;
use Tempest\Filesystem\StreamAccess;
use Tempest\Filesystem\StreamMode;

final class LocalStream implements Stream
{
    /**
     * @var resource
     */
    private $fileHandle;

    public function __construct(private readonly string $location)
    {
    }

    public function open(StreamMode $mode = StreamMode::OPEN_OR_CREATE, StreamAccess $access = StreamAccess::READ_WRITE): void
    {
        error_clear_last();

        $streamMode = $this->negotiateMode($mode, $access);

        $this->fileHandle = fopen($this->location, $streamMode);

        if ($this->fileHandle === false) {
            throw UnableToOpenStream::forLocation(
                location: $this->location,
                mode: $mode,
                access: $access,
                because: error_get_last()['message'] ?? ''
            );
        }
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
        error_clear_last();

        if (@fclose($this->fileHandle) === false) {
            throw UnableToCloseStream::forLocation(
                location: $this->location,
                because: error_get_last()['message']
            );
        }
    }

    /**
     * @see https://www.php.net/manual/en/function.fopen.php#refsect1-function.fopen-parameters
     */
    private function negotiateMode(StreamMode $mode, StreamAccess $access): string
    {
        return match ($mode) {
            StreamMode::APPEND => $access->canRead() ? 'a+' : 'a',
            StreamMode::CREATE => $access->canRead() ? 'c+' : 'c',
            StreamMode::CREATE_NEW => $access->canRead() ? 'x+' : 'x',
            StreamMode::OPEN => $access->canWrite() ? 'r+' : 'r',
            StreamMode::OPEN_OR_CREATE => $access->canWrite() ? 'c+' : 'r+',
            StreamMode::TRUNCATE => $access->canRead() ? 'w+' : 'w',
        };
    }
}
