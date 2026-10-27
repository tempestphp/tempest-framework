<?php

declare(strict_types=1);

namespace Tempest\Generation\Php;

use ReflectionClass;
use Tempest\Generation\Php\Exceptions\FileGenerationFailedException;
use Tempest\Support\Filesystem;
use Tempest\Support\Filesystem\Exceptions\FilesystemException;

final class ClassManipulator
{
    use ManipulatesPhpClasses;

    /**
     * @param string|class-string|ReflectionClass|PhpFile $source a file path, a class name, or the loaded file
     */
    public function __construct(string|ReflectionClass|PhpFile $source)
    {
        if ($source instanceof PhpFile) {
            $this->file = $source;
            $this->class = $source->getClass();

            return;
        }

        if (is_string($source) && is_file($source)) {
            $this->file = PhpFile::fromPath($source);
            $this->class = $this->file->getClass();

            return;
        }

        $reflection = is_string($source) ? new ReflectionClass($source) : $source;

        $this->file = PhpFile::fromPath($reflection->getFileName());
        $this->class = $this->file->getClass($reflection->getShortName());
    }

    /**
     * Save the class to a target file.
     *
     * @param string $path the path to save the class to.
     *
     * @throws FileGenerationFailedException if the file could not be written.
     */
    public function save(string $path): self
    {
        try {
            Filesystem\create_directory_for_file($path);
            Filesystem\write_file($path, $this->print());
        } catch (FilesystemException) {
            throw new FileGenerationFailedException(sprintf('The file "%s" could not be written.', $path));
        }

        return $this;
    }
}
