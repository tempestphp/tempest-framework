<?php

declare(strict_types=1);

namespace Tempest\Generation;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PsrPrinter;
use ReflectionClass;

final class ClassManipulator
{
    private ClassType $classType;

    private PhpFile $file;

    private string $namespace;

    private bool $simplifyImplements = false;

    private array $aliases = [];

    public function __construct(string|ReflectionClass $className)
    {
        $reflection = is_string($className)
            ? new ReflectionClass($className)
            : $className;

        $this->file = new PhpFile();
        $this->classType = ClassType::from($reflection->getName(), withBodies: true); // @phpstan-ignore-line
        $this->namespace = $reflection->getNamespaceName();
    }

    public function removeClassAttribute(string $attributeName): self
    {
        $attributes = $this->classType->getAttributes();

        foreach ($attributes as $key => $attribute) {
            if ($attribute->getName() === $attributeName) {
                unset($attributes[$key]);
            }
        }

        $this->classType->setAttributes($attributes);

        return $this;
    }

    public function setFinal(bool $final = true): self
    {
        $this->classType->setFinal($final);

        return $this;
    }

    public function setReadOnly(bool $readonly = true): self
    {
        $this->classType->setReadOnly($readonly);

        return $this;
    }

    public function setStrictTypes(bool $strictTypes = true): self
    {
        $this->file->setStrictTypes($strictTypes);

        return $this;
    }

    public function updateNamespace(string $namespace): self
    {
        $this->namespace = $namespace;

        return $this;
    }

    public function print(): string
    {
        $printer = new PsrPrinter();

        $file = clone $this->file;
        $namespace = $file->addNamespace($this->namespace);
        $namespace->add($this->classType);

        $this->simplifyClassNames($file);

        return $printer->printFile($file);
    }

    public function simplifyImplements(): self
    {
        $this->simplifyImplements = true;

        return $this;
    }

    public function setAlias(string $class, string $alias): self
    {
        if (isset($this->aliases[$class])) {
            unset($this->aliases[$class]);
        }

        $this->aliases[$class] = $alias;

        return $this;
    }

    private function simplifyClassNames(PhpFile $file): PhpFile
    {
        foreach ($file->getNamespaces() as $namespace) {
            foreach ($namespace->getClasses() as $class) {
                $types = [];

                if ($this->simplifyImplements) {
                    foreach ($class->getImplements() as $implement) {
                        $types[] = $implement;
                    }
                }

                foreach ($class->getAttributes() as $attribute) {
                    $types[] = $attribute->getName();
                }

                foreach ($class->getMethods() as $method) {
                    $types[] = $method->getReturnType(true);

                    array_map(function ($param) use (&$types): void {
                        $types[] = $param->getType(true);
                    }, $method->getParameters());

                    $methodBody = $method->getBody();
                    $fqcnMatches = $this->extractFqcnFromBody($methodBody);

                    foreach (array_filter($fqcnMatches) as $fqcn) {
                        $namespace->addUse($fqcn);
                    }
                }

                array_map(function ($param) use (&$types): void {
                    $types[] = $param->getType(true);
                }, $class->getProperties());

                foreach ($this->aliases as $class => $alias) {
                    $namespace->addUse($class, $alias);
                }

                foreach (array_filter($types) as $type) {
                    if (is_string($type)) {
                        $namespace->addUse($type);

                        continue;
                    }

                    foreach ($type->getTypes() as $subtype) {
                        if ($subtype->isClass() && ! $subtype->isClassKeyword()) {
                            $namespace->addUse((string) $subtype);
                        }
                    }
                }
            }
        }

        return $file;
    }

    private function extractFqcnFromBody(string $body): array
    {
        preg_match_all('/(?:\\\\?[A-Za-z_][\w\d_]*\\\\)+[A-Za-z_][\w\d_]*/', $body, $matches);

        return array_filter(array_unique(
            array_map(fn (string $fqcn) => rtrim(ltrim($fqcn, '\\'), ':'), $matches[0])
        ));
    }
}
