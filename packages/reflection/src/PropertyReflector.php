<?php

declare(strict_types=1);

namespace Tempest\Reflection;

use Error;
use PhpToken;
use ReflectionClass;
use ReflectionProperty as PHPReflectionProperty;

final class PropertyReflector implements Reflector
{
    use HasAttributes;

    /** @var array<string, array<string, string>> */
    private static array $useStatementCache = [];

    /** @var array<string, string> */
    private static array $resolvedTypeCache = [];

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

        if (! $doc || ! preg_match('/@var\s+(?:(?:array|list)<([\\\\\w]+)>|([\\\\\w]+)\[\])/', $doc, $match)) {
            return null;
        }

        $rawType = $match[1] !== '' ? $match[1] : $match[2];
        $typeName = ltrim($rawType, '\\');

        return str_contains($rawType, '\\')
            ? new TypeReflector($typeName)
            : new TypeReflector($this->resolveShortClassName($typeName));
    }

    private function resolveShortClassName(string $shortName): string
    {
        $declaringClass = $this->reflectionProperty->getDeclaringClass();
        $cacheKey = $declaringClass->getName() . '::' . $shortName;

        return self::$resolvedTypeCache[$cacheKey] ??= $this->doResolveShortClassName($shortName, $declaringClass);
    }

    private function doResolveShortClassName(string $shortName, ReflectionClass $declaringClass): string
    {
        $fileName = $declaringClass->getFileName();

        if ($fileName) {
            self::$useStatementCache[$fileName] ??= $this->parseUseStatements($fileName);

            if (isset(self::$useStatementCache[$fileName][$shortName])) {
                return self::$useStatementCache[$fileName][$shortName];
            }
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

    /** @return array<string, string> */
    private function parseUseStatements(string $fileName): array
    {
        $content = file_get_contents($fileName);

        if ($content === false) {
            return [];
        }

        $tokens = PhpToken::tokenize($content);
        $useStatements = [];
        $count = count($tokens);

        for ($i = 0; $i < $count; $i++) {
            $token = $tokens[$i];

            if ($token->is([T_CLASS, T_INTERFACE, T_TRAIT, T_ENUM])) {
                break;
            }

            if (! $token->is(T_USE)) {
                continue;
            }

            $i++;
            $this->skipWhitespaceTokens($tokens, $i, $count);

            if ($tokens[$i]->is([T_FUNCTION, T_CONST])) {
                continue;
            }

            $fqcn = $this->parseNamespacedName($tokens, $i, $count);
            $this->skipWhitespaceTokens($tokens, $i, $count);

            if ($tokens[$i]->text === '{') {
                $this->parseGroupUse($tokens, $i, $count, $fqcn, $useStatements);
                continue;
            }

            $alias = $this->parseAlias($tokens, $i, $count) ?? $this->getShortName($fqcn);
            $useStatements[$alias] = $fqcn;

            while ($i < $count && $tokens[$i]->text !== ';') {
                $i++;
            }
        }

        return $useStatements;
    }

    /** @param PhpToken[] $tokens */
    private function skipWhitespaceTokens(array $tokens, int &$i, int $count): void
    {
        while ($i < $count && $tokens[$i]->is([T_WHITESPACE, T_COMMENT])) {
            $i++;
        }
    }

    /** @param PhpToken[] $tokens */
    private function parseNamespacedName(array $tokens, int &$i, int $count): string
    {
        $name = '';

        while ($i < $count) {
            $token = $tokens[$i];

            if ($token->is([T_STRING, T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED, T_NS_SEPARATOR])) {
                $name .= $token->text;
                $i++;
            } elseif ($token->is(T_WHITESPACE)) {
                $i++;
            } else {
                break;
            }
        }

        return ltrim($name, '\\');
    }

    /** @param PhpToken[] $tokens */
    private function parseAlias(array $tokens, int &$i, int $count): ?string
    {
        if (! $tokens[$i]->is(T_AS)) {
            return null;
        }

        $i++;
        $this->skipWhitespaceTokens($tokens, $i, $count);

        if ($tokens[$i]->is(T_STRING)) {
            return $tokens[$i++]->text;
        }

        return null;
    }

    /**
     * @param PhpToken[] $tokens
     * @param array<string, string> $useStatements
     */
    private function parseGroupUse(array $tokens, int &$i, int $count, string $prefix, array &$useStatements): void
    {
        $i++;

        while ($i < $count && $tokens[$i]->text !== '}') {
            $this->skipWhitespaceTokens($tokens, $i, $count);

            if ($i >= $count) {
                break;
            }

            if ($tokens[$i]->is([T_FUNCTION, T_CONST])) {
                while ($i < $count && $tokens[$i]->text !== ',' && $tokens[$i]->text !== '}') {
                    $i++;
                }

                if ($i < $count && $tokens[$i]->text === ',') {
                    $i++;
                }

                continue;
            }

            $name = $this->parseNamespacedName($tokens, $i, $count);

            if ($name === '') {
                $i++;
                continue;
            }

            $this->skipWhitespaceTokens($tokens, $i, $count);

            if ($i >= $count) {
                break;
            }

            $alias = $this->parseAlias($tokens, $i, $count) ?? $this->getShortName($name);
            $useStatements[$alias] = $prefix . '\\' . $name;

            $this->skipWhitespaceTokens($tokens, $i, $count);

            if ($i < $count && $tokens[$i]->text === ',') {
                $i++;
            }
        }

        if ($i < $count) {
            $i++;
        }
    }

    private function getShortName(string $fqcn): string
    {
        $pos = strrpos($fqcn, '\\');

        return $pos === false ? $fqcn : substr($fqcn, $pos + 1);
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
