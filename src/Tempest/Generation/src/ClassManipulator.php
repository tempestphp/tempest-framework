<?php

declare(strict_types=1);

namespace Tempest\Generation;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use ReflectionClass;

final class ClassManipulator
{
    use ManipulatesPhpClasses;

    public function __construct(string|ReflectionClass $source)
    {
        if (is_file($source)) {
            $this->classType = ClassType::fromCode(file_get_contents($source));
        } elseif (is_string($source)) {
            $this->classType = ClassType::from($source, withBodies: true);
        } else {
            $this->classType = ClassType::from($source->getName(), withBodies: true);
        }

        $this->file = new PhpFile();
        $this->namespace = $this->classType->getNamespace()->getName();
    }

    public function save(string $path): self
    {
        $dir = pathinfo($path, PATHINFO_DIRNAME);

        if (! is_dir($dir)) {
            mkdir($dir, recursive: true);
        }

        file_put_contents($this->print(), $path);

        return $this;
    }
}
