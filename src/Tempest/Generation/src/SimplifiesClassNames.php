<?php

declare(strict_types=1);

namespace Tempest\Generation;

use Nette\PhpGenerator\PhpFile;
use function Tempest\Support\str;

/**
 * @internal
 */
trait SimplifiesClassNames
{
    private bool $simplifyImplements = true;

    private bool $simplifyClassNamesInBodies = true;

    private array $aliases = [];

    public function simplifyClassNamesInMethodBodies(bool $simplify = true): self
    {
        $this->simplifyClassNamesInBodies = $simplify;

        return $this;
    }

    public function simplifyImplements(bool $simplify = true): self
    {
        $this->simplifyImplements = $simplify;

        return $this;
    }

    public function addUse(string $use): self
    {
        $this->aliases[$use] = null;

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

                    if ($this->simplifyClassNamesInBodies) {
                        $methodBody = $method->getBody();
                        $fqcnMatches = $this->extractFqcnFromBody($methodBody);

                        foreach (array_filter($fqcnMatches) as $fqcn) {
                            $namespace->addUse($fqcn);

                            if (! str_contains($methodBody, "/*(n*/\\{$fqcn}")) {
                                $methodBody = str_replace(
                                    search: '\\'.$fqcn,
                                    replace: (string) str($fqcn)->afterLast('\\'),
                                    subject: $methodBody
                                );
                            }
                        }

                        $method->setBody($methodBody);
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
