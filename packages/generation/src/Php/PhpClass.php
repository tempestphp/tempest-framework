<?php

declare(strict_types=1);

namespace Tempest\Generation\Php;

use PhpParser\Modifiers;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use Tempest\Generation\Php\Exceptions\MethodWasMissing;

final readonly class PhpClass
{
    /**
     * @internal
     */
    public function __construct(
        private Stmt\Class_ $node,
    ) {}

    public function getName(): string
    {
        return $this->node->name->toString();
    }

    public function setName(string $name): self
    {
        $this->node->name = new Identifier($name);

        return $this;
    }

    public function setFinal(bool $final = true): self
    {
        return $this->setModifier(Modifiers::FINAL, $final);
    }

    public function setReadOnly(bool $readonly = true): self
    {
        return $this->setModifier(Modifiers::READONLY, $readonly);
    }

    public function setAbstract(bool $abstract = true): self
    {
        return $this->setModifier(Modifiers::ABSTRACT, $abstract);
    }

    public function setExtends(?string $class): self
    {
        $this->node->extends = $class !== null ? new Name\FullyQualified(ltrim($class, '\\')) : null;

        return $this;
    }

    public function addImplement(string $interface): self
    {
        if (array_any($this->node->implements, static fn (Name $implement) => Names::refersTo($implement, $interface))) {
            return $this;
        }

        $this->node->implements[] = new Name\FullyQualified(ltrim($interface, '\\'));

        return $this;
    }

    public function addTrait(string $trait): self
    {
        $traitUse = new Stmt\TraitUse([new Name\FullyQualified(ltrim($trait, '\\'))]);
        $lastTraitUse = null;

        foreach ($this->node->stmts as $index => $stmt) {
            if (! $stmt instanceof Stmt\TraitUse) {
                continue;
            }

            $lastTraitUse = $index;
        }

        if ($lastTraitUse !== null) {
            array_splice($this->node->stmts, $lastTraitUse + 1, 0, [$traitUse]);
        } else {
            array_splice($this->node->stmts, 0, 0, $this->node->stmts === [] ? [$traitUse] : [$traitUse, Printer::blankLine()]);
        }

        return $this;
    }

    /**
     * @param array<array-key, mixed> $arguments
     */
    public function addAttribute(string $name, array $arguments = []): self
    {
        Attributes::add($this->node, $name, $arguments);

        return $this;
    }

    public function removeAttribute(string $name): self
    {
        Attributes::remove($this->node, $name);

        return $this;
    }

    public function setComment(?string $comment): self
    {
        $this->node->setAttribute('comments', $comment !== null ? [DocComment::from($comment)] : []);

        return $this;
    }

    public function hasMethod(string $name): bool
    {
        return $this->findMethod($name) !== null;
    }

    public function getMethod(string $name): PhpMethod
    {
        $method = $this->findMethod($name) ?? throw new MethodWasMissing($this->getName(), $name);

        return new PhpMethod($method);
    }

    /**
     * Adds a public method, replacing any existing method with the same name.
     */
    public function addMethod(string $name): PhpMethod
    {
        $method = new Stmt\ClassMethod($name, ['flags' => Modifiers::PUBLIC, 'stmts' => []]);
        $index = $this->findMethodIndex($name);

        if ($index !== null) {
            $this->node->stmts[$index] = $method;
        } else {
            array_push($this->node->stmts, ...$this->node->stmts === [] ? [$method] : [Printer::blankLine(), $method]);
        }

        return new PhpMethod($method);
    }

    public function removeMethod(string $name): self
    {
        $index = $this->findMethodIndex($name);

        if ($index !== null) {
            array_splice($this->node->stmts, $index, 1);
        }

        return $this;
    }

    private function setModifier(int $modifier, bool $enabled): self
    {
        $this->node->flags = $enabled
            ? $this->node->flags | $modifier
            : $this->node->flags & ~$modifier;

        return $this;
    }

    private function findMethod(string $name): ?Stmt\ClassMethod
    {
        return array_find($this->node->getMethods(), static fn (Stmt\ClassMethod $method) => $method->name->toLowerString() === strtolower($name));
    }

    private function findMethodIndex(string $name): ?int
    {
        return array_find_key($this->node->stmts, static fn (Stmt $stmt) => $stmt instanceof Stmt\ClassMethod && $stmt->name->toLowerString() === strtolower($name));
    }
}
