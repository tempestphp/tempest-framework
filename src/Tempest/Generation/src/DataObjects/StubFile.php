<?php

declare(strict_types=1);

namespace Tempest\Generation\DataObjects;

use Throwable;
use Tempest\Generation\Exceptions\ClassNotFoundException;
use Tempest\Generation\Enums\StubFileType;
use Tempest\Generation\ClassManipulator;
use Nette\InvalidStateException;
use Tempest\Reflection\ClassReflector;

/**
 * Represents a file that is to be generated.
 */
final class StubFile
{
    public function __construct(
        public readonly string $filePath,
        public readonly StubFileType $type,
    ) {}

    public static function fromFilePath(string $filePath): self {
        // Every file that can't be constructed in class manipulator is considered raw
        if ( ! file_exists($filePath)) {
            throw new \Exception(sprintf('The file "%s" does not exist.', $filePath));
        }

        try {
            new ClassManipulator($filePath);

            return new self($filePath, StubFileType::CLASS_FILE);
        } catch (InvalidStateException) {
            return new self($filePath, StubFileType::RAW_FILE);
        }
    }

    public static function fromClassString(string $className): self {
        try {
            $classReflector = new ClassReflector($className);

            return new self(
                filePath: $classReflector->getFileName(),
                type: StubFileType::CLASS_FILE,
            );
        } catch (Throwable) {
            throw new ClassNotFoundException(sprintf('The class "%s" does not exist.', $className));
        }
    }
}
