<?php

declare(strict_types=1);

namespace Tempest\Generation\DataObjects;

use Exception;
use Nette\InvalidStateException;
use Tempest\Generation\ClassManipulator;
use Tempest\Generation\Enums\StubFileType;

/**
 * Represents a file that is to be generated.
 */
final class StubFile
{
    public function __construct(
        public readonly string $filePath,
        public readonly StubFileType $type,
    ) {
    }

    /**
     * @param string|class-string $pathOrClass The path of the file or the class-string
     */
    public static function from(string $pathOrClass): self
    {
        try {
            new ClassManipulator($pathOrClass);

            return new self(
                filePath: $pathOrClass,
                type: StubFileType::CLASS_FILE,
            );
        } catch (InvalidStateException) {
            if (! file_exists($pathOrClass)) {
                throw new Exception(sprintf('The file "%s" does not exist.', $pathOrClass));
            }

            return new self(
                filePath: $pathOrClass,
                type: StubFileType::RAW_FILE,
            );
        }
    }
}
