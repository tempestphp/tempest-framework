<?php

declare(strict_types=1);

namespace Tempest\Reflection;

use Error;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use ReflectionProperty as PHPReflectionProperty;

final class PropertyReflector implements Reflector
{
    use HasAttributes;

    /** @var array<string, array<string, string>> */
    private static array $useStatementCache = [];

    public function __construct(
        private readonly PHPReflectionProperty $reflectionProperty,
    ) {}

    public static function fromParts(string|object $class, string $name): self
    {
        return new self(new PHPReflectionProperty($class, $name));
    }

    public function getReflection(): PHPReflectionProperty
    {
        return $this->reflectionProperty;
    }

    public function getValue(object $object): mixed
    {
        return $this->reflectionProperty->getValue($object);
    }

    public function setValue(object $object, mixed $value): void
    {
        $this->reflectionProperty->setValue($object, $value);
    }

    public function isInitialized(object $object): bool
    {
        return $this->reflectionProperty->isInitialized($object);
    }

    public function accepts(mixed $input): bool
    {
        return $this->getType()->accepts($input);
    }

    public function getClass(): ClassReflector
    {
        return new ClassReflector($this->reflectionProperty->getDeclaringClass());
    }

    public function getType(): TypeReflector
    {
        return new TypeReflector($this->reflectionProperty);
    }

    public function isIterable(): bool
    {
        return $this->getType()->isIterable();
    }

    public function isPromoted(): bool
    {
        return $this->reflectionProperty->isPromoted();
    }

    public function isNullable(): bool
    {
        return $this->getType()->isNullable();
    }

    public function isPrivate(): bool
    {
        return $this->reflectionProperty->isPrivate();
    }

    public function isProtected(): bool
    {
        return $this->reflectionProperty->isProtected();
    }

    public function isPublic(): bool
    {
        return $this->reflectionProperty->isPublic();
    }

    public function isReadonly(): bool
    {
        return $this->reflectionProperty->isReadOnly();
    }

    public function getIterableType(): ?TypeReflector
    {
        $doc = $this->reflectionProperty->getDocComment();

        if (! $doc) {
            return null;
        }

        preg_match('/@var ([\\\\\w]+)\[]/', $doc, $match);

        if (! isset($match[1])) {
            return null;
        }

        $typeName = ltrim($match[1], '\\');

        if (str_contains($match[1], '\\')) {
            return new TypeReflector($typeName);
        }

        return new TypeReflector($this->resolveShortClassName($typeName));
    }

    private function resolveShortClassName(string $shortName): string
    {
        if (class_exists($shortName)) {
            return $shortName;
        }

        $declaringClass = $this->reflectionProperty->getDeclaringClass();
        $fileName = $declaringClass->getFileName();

        if ($fileName && ($resolved = $this->resolveFromUseStatements($fileName, $shortName))) {
            return $resolved;
        }

        $namespace = $declaringClass->getNamespaceName();

        if ($namespace !== '') {
            $fqcn = $namespace . '\\' . $shortName;

            if (class_exists($fqcn)) {
                return $fqcn;
            }
        }

        return $shortName;
    }

    private function resolveFromUseStatements(string $fileName, string $shortName): ?string
    {
        $useStatements = $this->getUseStatements($fileName);

        return $useStatements[$shortName] ?? null;
    }

    /** @return array<string, string> */
    private function getUseStatements(string $fileName): array
    {
        return self::$useStatementCache[$fileName] ??= $this->parseUseStatements($fileName);
    }

    /** @return array<string, string> */
    private function parseUseStatements(string $fileName): array
    {
        $content = file_get_contents($fileName);

        if ($content === false) {
            return [];
        }

        $ast = new ParserFactory()
            ->createForNewestSupportedVersion()
            ->parse($content);

        if ($ast === null) {
            return [];
        }

        $useStatements = [];
        $traverser = new NodeTraverser();

        $traverser->addVisitor(new class($useStatements) extends NodeVisitorAbstract {
            public function __construct(
                private array &$useStatements,
            ) {}

            public function enterNode(Node $node): null
            {
                match (true) {
                    $node instanceof Node\Stmt\Use_ && $node->type === Node\Stmt\Use_::TYPE_NORMAL => $this->extractUseItems($node->uses),
                    $node instanceof Node\Stmt\GroupUse => $this->extractGroupUseItems($node),
                    default => null,
                };

                return null;
            }

            /** @param Node\UseItem[] $uses */
            private function extractUseItems(array $uses, string $prefix = ''): void
            {
                foreach ($uses as $use) {
                    $fqcn = $prefix . $use->name->toString();
                    $alias = $use->alias->name ?? $use->name->getLast();
                    $this->useStatements[$alias] = $fqcn;
                }
            }

            private function extractGroupUseItems(Node\Stmt\GroupUse $node): void
            {
                $prefix = $node->prefix->toString() . '\\';

                foreach ($node->uses as $use) {
                    if ($use->type !== Node\Stmt\Use_::TYPE_NORMAL) {
                        continue;
                    }

                    $fqcn = $prefix . $use->name->toString();
                    $alias = $use->alias->name ?? $use->name->getLast();
                    $this->useStatements[$alias] = $fqcn;
                }
            }
        });

        $traverser->traverse($ast);

        return $useStatements;
    }

    public function isUninitialized(object $object): bool
    {
        return ! $this->reflectionProperty->isInitialized($object);
    }

    public function isVirtual(): bool
    {
        return $this->reflectionProperty->isVirtual();
    }

    public function unset(object $object): void
    {
        unset($object->{$this->getName()});
    }

    public function set(object $object, mixed $value): void
    {
        $this->reflectionProperty->setValue($object, $value);
    }

    public function get(object $object, mixed $default = null): mixed
    {
        try {
            return $this->reflectionProperty->getValue($object) ?? $default;
        } catch (Error $error) {
            return $default ?? throw $error;
        }
    }

    public function getName(): string
    {
        return $this->reflectionProperty->getName();
    }

    public function hasDefaultValue(): bool
    {
        $constructorParameters = [];

        foreach ($this->getClass()->getConstructor()?->getParameters() ?? [] as $parameter) {
            $constructorParameters[$parameter->getName()] = $parameter;
        }

        $hasDefaultValue = $this->reflectionProperty->hasDefaultValue();

        $hasPromotedDefaultValue = $this->isPromoted() && $constructorParameters[$this->getName()]->isDefaultValueAvailable();

        return $hasDefaultValue || $hasPromotedDefaultValue;
    }
}
