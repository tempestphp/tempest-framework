<?php

declare(strict_types=1);

namespace Tempest\Testing\BypassMock;

use RuntimeException;

final class Wrapper
{
    public const Protocol = 'file';

    /** @var resource|null */
    public $context;

    /** @var resource|null */
    public $handle;

    public function stream_open(string $path, string $mode, int $options, ?string &$openedPath): bool
    {
        if (is_dir($path)) {
            return false;
        }

        if (! $this->open_the_stream($path, $mode, $options, $openedPath)) {
            return false;
        }

        if ($mode === 'rb' && pathinfo($path, PATHINFO_EXTENSION) === 'php') {
            $content = '';

            while (! $this->stream_eof()) {
                $content .= $this->stream_read(8192);
            }

            $this->stream_filter(Filter::NAME);
            $this->stream_seek(0);
        }

        return true;
    }

    private function open_the_stream(string $path, string $mode, int $options = 0, ?string &$openedPath = null): bool
    {
        $usePath = (bool) ($options & STREAM_USE_PATH);

        $this->handle = $this->context
            ? $this->native('fopen', $path, $mode, $usePath, $this->context)
            : $this->native('fopen', $path, $mode, $usePath);

        return (bool) $this->handle;
    }

    public function dir_closedir(): void
    {
        closedir($this->handle);
    }

    public function dir_opendir(string $path, int $options): bool
    {
        $this->handle = $this->context
            ? $this->native('opendir', $path, $this->context)
            : $this->native('opendir', $path);

        return (bool) $this->handle;
    }

    public function dir_readdir()
    {
        return readdir($this->handle);
    }

    public function dir_rewinddir(): bool
    {
        rewinddir($this->handle);

        return true;
    }

    public function mkdir(string $path, int $mode, int $options): bool
    {
        $recursive = (bool) ($options & STREAM_MKDIR_RECURSIVE);

        return $this->context
            ? $this->native('mkdir', $path, $mode, $recursive, $this->context)
            : $this->native('mkdir', $path, $mode, $recursive);
    }

    public function rename(string $pathFrom, string $pathTo): bool
    {
        return $this->context
            ? $this->native('rename', $pathFrom, $pathTo, $this->context)
            : $this->native('rename', $pathFrom, $pathTo);
    }

    public function rmdir(string $path, int $options): bool
    {
        return $this->context
            ? $this->native('rmdir', $path, $this->context)
            : $this->native('rmdir', $path);
    }

    public function stream_cast(int $castAs): mixed
    {
        return $this->handle;
    }

    public function stream_close(): void
    {
        fclose($this->handle);
    }

    public function stream_eof(): bool
    {
        return feof($this->handle);
    }

    public function stream_flush(): bool
    {
        return fflush($this->handle);
    }

    public function stream_lock(int $operation): bool
    {
        return $operation
            ? flock($this->handle, $operation)
            : true;
    }

    public function stream_metadata(string $path, int $option, mixed $value): bool
    {
        switch ($option) {
            case STREAM_META_TOUCH:
                return $this->native('touch', $path, $value[0] ?? time(), $value[1] ?? time());
            case STREAM_META_OWNER_NAME:
            case STREAM_META_OWNER:
                return $this->native('chown', $path, $value);
            case STREAM_META_GROUP_NAME:
            case STREAM_META_GROUP:
                return $this->native('chgrp', $path, $value);
            case STREAM_META_ACCESS:
                return $this->native('chmod', $path, $value);
            default:
                return false;
        }
    }

    public function stream_read(int $count): string|false
    {
        return fread($this->handle, $count);
    }

    public function stream_seek(int $offset, int $whence = SEEK_SET): bool
    {
        return fseek($this->handle, $offset, $whence) === 0;
    }

    public function stream_set_option(int $option, int $arg1, ?int $arg2)
    {
        switch ($option) {
            case STREAM_OPTION_BLOCKING:
                return stream_set_blocking($this->handle, (bool) $arg1);
            case STREAM_OPTION_READ_BUFFER:
                return stream_set_read_buffer($this->handle, $arg2);
            case STREAM_OPTION_WRITE_BUFFER:
                return stream_set_write_buffer($this->handle, $arg2);
            case STREAM_OPTION_READ_TIMEOUT:
                return stream_set_timeout($this->handle, $arg1, $arg2);
            default:
                return false;
        }
    }

    public function stream_stat(): array|false
    {
        return fstat($this->handle);
    }

    public function stream_tell(): int
    {
        return ftell($this->handle);
    }

    public function stream_truncate(int $newSize): bool
    {
        return ftruncate($this->handle, $newSize);
    }

    public function stream_write(string $data): int
    {
        return fwrite($this->handle, $data);
    }

    public function stream_filter(string $filter): bool
    {
        return (bool) $this->native('stream_filter_append', $this->handle, $filter);
    }

    public function unlink(string $path): bool
    {
        return $this->native('unlink', $path);
    }

    public function url_stat(string $path, int $flags): array|false
    {
        if ($flags & STREAM_URL_STAT_QUIET) {
            set_error_handler(function () {
                return true;
            });
        }

        try {
            $func = $flags & STREAM_URL_STAT_LINK ? 'lstat' : 'stat';

            return $this->native($func, $path);
        } catch (RuntimeException $e) {
            // SplFileInfo::isFile throws exception
            return false;
        } finally {
            if ($flags & STREAM_URL_STAT_QUIET) {
                restore_error_handler();
            }
        }
    }

    private function native(string $func): mixed
    {
        stream_wrapper_restore(self::Protocol);

        try {
            return $func(...array_slice(func_get_args(), 1));
        } finally {
            stream_wrapper_unregister(self::Protocol);
            stream_wrapper_register(self::Protocol, self::class);
        }
    }
}
