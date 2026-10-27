<?php

declare(strict_types=1);

namespace Tempest\Generation\Php;

use Tempest\Generation\Php\Exceptions\GeneratedClassWasMissingANamespace;

/**
 * @internal
 */
trait ManipulatesPhpClasses
{
    protected PhpFile $file;

    protected PhpClass $class;

    /**
     * @param array<string, string> $parameters parameter names mapped to their types
     */
    public function addMethod(
        string $name,
        string $body = '',
        array $parameters = [],
        string $returnType = 'void',
    ): self {
        $method = $this->class
            ->addMethod($name)
            ->setReturnType($returnType)
            ->setBody($body);

        foreach ($parameters as $parameter => $type) {
            $method->addParameter($parameter, $type);
        }

        return $this;
    }

    public function setClassName(string $name): self
    {
        $this->class->setName($name);

        return $this;
    }

    public function removeMethod(string $name): self
    {
        $this->class->removeMethod($name);

        return $this;
    }

    /**
     * @param array<array-key, mixed> $arguments
     */
    public function addMethodAttribute(string $method, string $attribute, array $arguments = []): self
    {
        $this->class->getMethod($method)->addAttribute($attribute, $arguments);

        return $this;
    }

    /**
     * @param array<array-key, mixed> $arguments
     */
    public function addClassAttribute(string $attributeName, array $arguments = []): self
    {
        $this->class->addAttribute($attributeName, $arguments);

        return $this;
    }

    public function removeClassAttribute(string $attributeName): self
    {
        $this->class->removeAttribute($attributeName);

        return $this;
    }

    public function setStrictTypes(bool $strictTypes = true): self
    {
        $this->file->setStrictTypes($strictTypes);

        return $this;
    }

    public function setFinal(bool $final = true): self
    {
        $this->class->setFinal($final);

        return $this;
    }

    public function setReadOnly(bool $readonly = true): self
    {
        $this->class->setReadOnly($readonly);

        return $this;
    }

    public function setAbstract(bool $abstract = true): self
    {
        $this->class->setAbstract($abstract);

        return $this;
    }

    public function setFileComment(?string $comment): self
    {
        $this->file->setComment($comment);

        return $this;
    }

    public function setClassComment(?string $comment): self
    {
        $this->class->setComment($comment);

        return $this;
    }

    public function addImplement(string $interface): self
    {
        $this->class->addImplement($interface);

        return $this;
    }

    public function addExtend(string $class): self
    {
        $this->class->setExtends($class);

        return $this;
    }

    public function addTrait(string $trait): self
    {
        $this->class->addTrait($trait);

        return $this;
    }

    public function setNamespace(string $namespace): self
    {
        $this->file->setNamespace($namespace);

        return $this;
    }

    public function simplifyClassNamesInMethodBodies(bool $simplify = true): self
    {
        $this->file->simplifyNamesInBodies($simplify);

        return $this;
    }

    public function simplifyImplements(bool $simplify = true): self
    {
        $this->file->simplifyImplements($simplify);

        return $this;
    }

    public function addUse(string $use): self
    {
        $this->file->addUse($use);

        return $this;
    }

    public function setAlias(string $class, string $alias): self
    {
        $this->file->addUse($class, $alias);

        return $this;
    }

    public function getClassName(): string
    {
        return ltrim($this->file->getNamespace() . '\\' . $this->class->getName(), '\\');
    }

    public function print(): string
    {
        if (! $this->file->getNamespace()) {
            throw new GeneratedClassWasMissingANamespace();
        }

        return $this->file->print();
    }
}
