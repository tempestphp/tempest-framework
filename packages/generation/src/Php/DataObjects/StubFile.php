<?php

declare(strict_types=1);

namespace Tempest\Generation\Php\DataObjects;

use InvalidArgumentException;
use PhpParser\Node\Stmt;
use ReflectionClass;
use Tempest\Generation\Php\Exceptions\PhpCodeWasInvalid;
use Tempest\Generation\Php\SourceParser;
use Tempest\Generation\Php\StubFileType;
use Tempest\Support\Filesystem;

/**
 * Represents a file that is to be generated.
 */
final readonly class StubFile
{
    public function __construct(
        public string $filePath,
        public StubFileType $type,
    ) {}

    /**
     * @param string|class-string $pathOrClass The path of the file or the class-string
     */
    public static function from(string $pathOrClass): self
    {
        if (class_exists($pathOrClass)) {
            return new self(
                filePath: new ReflectionClass($pathOrClass)->getFileName(),
                type: StubFileType::CLASS_FILE,
            );
        }

        if (! is_file($pathOrClass)) {
            throw new InvalidArgumentException(sprintf('The file "%s" does not exist.', $pathOrClass));
        }

        return new self(
            filePath: $pathOrClass,
            type: self::declaresClass($pathOrClass) ? StubFileType::CLASS_FILE : StubFileType::RAW_FILE,
        );
    }

    /**
     * Whether the file is valid PHP that declares a class where `PhpFile::getClass()` finds it:
     * at the top level, or in its first namespace.
     */
    private static function declaresClass(string $path): bool
    {
        try {
            [$stmts] = SourceParser::parse(Filesystem\read_file($path));
        } catch (PhpCodeWasInvalid) {
            return false;
        }

        foreach ($stmts as $stmt) {
            if (! $stmt instanceof Stmt\Namespace_) {
                continue;
            }

            $stmts = $stmt->stmts;

            break;
        }

        return array_any($stmts, static fn (Stmt $stmt) => $stmt instanceof Stmt\Class_);
    }
}
