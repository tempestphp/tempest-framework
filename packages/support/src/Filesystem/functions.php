<?php

declare(strict_types=1);

namespace Tempest\Support\Filesystem {
    use FilesystemIterator;

    use function copy as php_copy;
    use function dirname;
    use function file_exists;
    use function fileperms;
    use function is_dir;
    use function is_executable as php_is_executable;
    use function is_file as php_is_file;
    use function is_link as php_is_link;
    use function is_readable as php_is_readable;
    use function is_writable as php_is_writable;
    use function mkdir;
    use function readlink;
    use function Tempest\Support\Arr\partition;
    use function Tempest\Support\Arr\values;
    use function Tempest\Support\box;
    use function touch;

    /**
     * Gets a parent directory path.
     */
    function get_directory(string $node, int $levels = 1): string
    {
        return dirname($node, $levels);
    }

    /**
     * Copies a file from `$source` to `$destination`.
     */
    function copy_file(string $source, string $destination, bool $overwrite = false): void
    {
        $destination_exists = namespace\is_file($destination);

        if (! $overwrite && $destination_exists) {
            return;
        }

        if (namespace\is_directory($source)) {
            throw new Exceptions\PathWasNotAFile($source);
        }

        if (! namespace\is_file($source)) {
            throw Exceptions\PathWasNotFound::forFile($source);
        }

        if (! namespace\is_readable($source)) {
            throw Exceptions\PathWasNotReadable::forFile($source);
        }

        namespace\create_directory_for_file($destination);

        [$result, $errorMessage] = box(static fn (): bool => php_copy($source, $destination));

        if ($result === false) {
            throw new Exceptions\RuntimeException(
                sprintf('Failed to copy source file "%s" to destination "%s": %s', $source, $destination, $errorMessage),
            );
        }
    }

    /**
     * Writes the specified `$content` to the specified `$filename`.
     */
    function write_file(string $filename, mixed $content, int $flags = 0): void
    {
        namespace\create_directory_for_file($filename);

        [$result, $errorMessage] = box(static fn (): int|false => file_put_contents($filename, $content, $flags));

        if (false === $result) {
            throw new Exceptions\RuntimeException(sprintf(
                'Failed to write to file "%s": %s.',
                $filename,
                $errorMessage ?? 'internal error',
            ));
        }
    }

    /**
     * Reads the content of the specified `$filename`.
     */
    function read_file(string $filename): string
    {
        if (! namespace\exists($filename)) {
            throw Exceptions\PathWasNotFound::forFile($filename);
        }

        if (! namespace\is_readable($filename)) {
            throw Exceptions\PathWasNotReadable::forFile($filename);
        }

        [$result, $message] = box(static fn (): false|string => file_get_contents($filename));

        if (false === $result) {
            throw new Exceptions\RuntimeException(sprintf(
                'Failed to read file "%s": %s',
                $filename,
                $message ?? 'internal error',
            ));
        }

        return $result;
    }

    /**
     * Ensures that the specified directory exists.
     */
    function ensure_directory_exists(string $directory): void
    {
        if (! namespace\exists($directory)) {
            namespace\create_directory($directory);
        }
    }

    /**
     * Creates the directory specified by $directory.
     *
     * @mago-expect best-practices/no-boolean-literal-comparison
     */
    function create_directory(string $directory, int $permissions = 0o777): void
    {
        if (namespace\is_directory($directory)) {
            return;
        }

        [$result, $errorMessage] = box(static fn (): bool => mkdir($directory, $permissions, recursive: true));

        if ($result === false && ! namespace\is_directory($directory)) { // @phpstan-ignore booleanNot.alwaysTrue
            throw new Exceptions\RuntimeException(sprintf(
                'Failed to create directory "%s": %s.',
                $directory,
                $errorMessage ?? 'internal error',
            ));
        }
    }

    /**
     * Creates the directory where the $filename is or will be stored.
     *
     * @return non-empty-string
     */
    function create_directory_for_file(string $filename, int $permissions = 0o777): string
    {
        $directory = namespace\get_directory($filename);
        namespace\create_directory($directory, $permissions);

        return $directory;
    }

    /**
     * Creates the file specified by $filename.
     *
     * @mago-expect best-practices/no-boolean-literal-comparison
     * @mago-expect best-practices/no-else-clause
     */
    function create_file(string $filename, ?int $time = null, ?int $accessTime = null): void
    {
        if (null === $accessTime && null === $time) {
            $fun = static fn (): bool => touch($filename);
        } elseif (null === $accessTime) {
            $fun = static fn (): bool => touch($filename, $time);
        } else {
            $time ??= $accessTime;
            $fun = static fn (): bool => touch($filename, $time, max($accessTime, $time));
        }

        namespace\create_directory_for_file($filename);

        [$result, $errorMessage] = box($fun);

        if (false === $result && ! namespace\is_file($filename)) {
            throw new Exceptions\RuntimeException(sprintf(
                'Failed to create file "%s": %s.',
                $filename,
                $errorMessage ?? 'internal error',
            ));
        }
    }

    /**
     * Checks whether `$path` exists.
     */
    function exists(string $path): bool
    {
        return file_exists($path);
    }

    /**
     * Deletes the file or directory at the specified `$path`.
     */
    function delete(string $path, bool $recursive = true): void
    {
        if (! namespace\exists($path)) {
            return;
        }

        if (namespace\is_file($path)) {
            namespace\delete_file($path);
        } elseif (namespace\is_directory($path)) {
            namespace\delete_directory($path, $recursive);
        }
    }

    /**
     * Deletes the specified `$file`.
     *
     * @mago-expect best-practices/no-boolean-literal-comparison
     */
    function delete_file(string $file): void
    {
        if (namespace\is_symbolic_link($file)) {
            [$result, $errorMessage] = box(static fn (): bool => unlink($file));

            if ($result === false && namespace\is_symbolic_link($file)) { // @phpstan-ignore booleanAnd.rightAlwaysTrue
                throw new Exceptions\RuntimeException(sprintf(
                    'Failed to delete symbolic link "%s": %s.',
                    $file,
                    $errorMessage ?? 'internal error',
                ));
            }

            return;
        }

        if (! namespace\exists($file)) {
            throw Exceptions\PathWasNotFound::forFile($file);
        }

        if (! namespace\is_file($file)) {
            throw new Exceptions\PathWasNotAFile($file);
        }

        [$result, $errorMessage] = box(static fn (): bool => unlink($file));

        if ($result === false && namespace\is_file($file)) { // @phpstan-ignore booleanAnd.rightAlwaysTrue
            throw new Exceptions\RuntimeException(sprintf(
                'Failed to delete file "%s": %s.',
                $file,
                $errorMessage ?? 'internal error',
            ));
        }
    }

    /**
     * Gets the permissions of the file or directory at the specified `$path`.
     *
     * @mago-expect best-practices/no-boolean-literal-comparison
     */
    function get_permissions(string $path): int
    {
        if (! namespace\exists($path)) {
            throw Exceptions\PathWasNotFound::forPath($path);
        }

        [$result, $message] = box(static fn (): int|false => fileperms($path));

        if (false === $result) {
            throw new Exceptions\RuntimeException(sprintf(
                'Failed to retrieve permissions of file "%s": %s',
                $path,
                $message ?? 'internal error',
            ));
        }

        return $result;
    }

    /**
     * Cleans the specified `$directory` by deleting its contents, optionally creating it if it doesn't exist.
     */
    function ensure_directory_empty(string $directory): void
    {
        if (namespace\exists($directory) && ! namespace\is_directory($directory)) {
            throw new Exceptions\PathWasNotADirectory($directory);
        }

        if (! namespace\is_directory($directory)) {
            namespace\create_directory($directory);
            return;
        }

        $permissions = PHP_OS_FAMILY === 'Windows'
            ? namespace\get_permissions($directory)
            : 0o777;

        namespace\delete_directory($directory, recursive: true);
        namespace\create_directory($directory, $permissions);
    }

    /**
     * Deletes the specified $directory.
     *
     * @mago-expect best-practices/no-boolean-literal-comparison
     * @mago-expect best-practices/no-else-clause
     */
    function delete_directory(string $directory, bool $recursive = true): void
    {
        if ($recursive && ! namespace\is_symbolic_link($directory)) {
            [$symbolicLinks, $files] = partition(
                iterable: list_directory($directory),
                predicate: static fn (string $node): bool => namespace\is_symbolic_link($node),
            );

            foreach ($symbolicLinks as $symbolicLink) {
                namespace\delete_file($symbolicLink);
            }

            foreach ($files as $node) {
                if (! namespace\is_directory($node)) {
                    namespace\delete_file($node);
                } else {
                    namespace\delete_directory($node, recursive: true);
                }
            }
        } else {
            if (! namespace\exists($directory)) {
                throw Exceptions\PathWasNotFound::forDirectory($directory);
            }

            if (! namespace\is_directory($directory)) {
                throw new Exceptions\PathWasNotADirectory($directory);
            }
        }

        [$result, $errorMessage] = box(static fn (): bool => rmdir($directory));

        if (false === $result && namespace\is_directory($directory)) {
            throw new Exceptions\RuntimeException(sprintf(
                'Failed to delete directory "%s": %s.',
                $directory,
                $errorMessage ?? 'internal error',
            ));
        }
    }

    /**
     * Checks whether $path exists and is a regular file or a link to one.
     */
    function is_file(string $path): bool
    {
        return php_is_file($path);
    }

    /**
     * Checks whether $path exists and is readable.
     */
    function is_readable(string $path): bool
    {
        return php_is_readable($path);
    }

    /**
     * Checks whether $path exists and is a symbolic link.
     */
    function is_symbolic_link(string $path): bool
    {
        return php_is_link($path);
    }

    /**
     * Checks whether $path exists and is writable.
     */
    function is_writable(string $path): bool
    {
        return php_is_writable($path);
    }

    /**
     * Checks whether $path exists and is an executable file
     * or a directory with `execute` permission.
     */
    function is_executable(string $path): bool
    {
        return php_is_executable($path);
    }

    /**
     * Checks whether $path exists and is a directory.
     */
    function is_directory(string $path): bool
    {
        return is_dir($path);
    }

    /**
     * Returns an array of files and directories inside the specified directory.
     *
     * @return array<non-empty-string>
     */
    function list_directory(string $directory): array
    {
        if (! namespace\exists($directory)) {
            throw Exceptions\PathWasNotFound::forDirectory($directory);
        }

        if (! namespace\is_directory($directory)) {
            throw new Exceptions\PathWasNotADirectory($directory);
        }

        if (! namespace\is_readable($directory)) {
            throw Exceptions\PathWasNotReadable::forDirectory($directory);
        }

        /** @var array<non-empty-string> */
        return values(new FilesystemIterator(
            $directory,
            FilesystemIterator::CURRENT_AS_PATHNAME | FilesystemIterator::SKIP_DOTS,
        ));
    }

    /**
     * Returns the target of a symbolic link.
     *
     * @mago-expect best-practices/no-boolean-literal-comparison
     */
    function read_symbolic_link(string $path): string
    {
        if (! namespace\exists($path)) {
            throw Exceptions\PathWasNotFound::forSymbolicLink($path);
        }

        if (! namespace\is_symbolic_link($path)) {
            throw new Exceptions\PathWasNotASymbolicLink($path);
        }

        [$result, $message] = box(static fn (): false|string => readlink($path));

        if (false === $result) {
            throw new Exceptions\RuntimeException(sprintf(
                'Failed to retrieve the target of symbolic link "%s": %s',
                $path,
                $message ?? 'internal error',
            ));
        }

        return $result;
    }
}
