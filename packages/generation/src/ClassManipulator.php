<?php

declare(strict_types=1);

namespace Tempest\Generation;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use ReflectionClass;
use Tempest\Generation\Exceptions\FileGenerationFailedException;
use Tempest\Support\Filesystem;
use Tempest\Support\Filesystem\Exceptions\FilesystemException;

final class ClassManipulator
{
    use ManipulatesPhpClasses;

    public function __construct(string|ReflectionClass $source)
    {
        if (is_file($source)) {
            /** @phpstan-ignore-next-line */
            $this->classType = ClassType::fromCode(Filesystem\read_file($source));
        } elseif (is_string($source)) {
            /** @phpstan-ignore-next-line */
            $this->classType = ClassType::from($source, withBodies: true);
        } else {
            /** @phpstan-ignore-next-line */
            $this->classType = ClassType::from($source->getName(), withBodies: true);
        }

        $this->file = new PhpFile();
        $this->namespace = $this->classType->getNamespace()->getName();
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
        $dir = pathinfo($path, PATHINFO_DIRNAME);

        if (! is_dir($dir)) {
            mkdir($dir, recursive: true);
        }

        try {
            Filesystem\write_file($path, $this->print());
        } catch (FilesystemException) {
            throw new FileGenerationFailedException(sprintf('The file "%s" could not be written.', $path));
        }

        return $this;
    }
}
