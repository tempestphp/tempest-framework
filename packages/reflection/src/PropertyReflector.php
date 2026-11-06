<?php

declare(strict_types=1);

namespace Tempest\Reflection;

use Error;
use PropertyHookType;
use ReflectionMethod;
use ReflectionProperty as PHPReflectionProperty;
use Stringable;

final readonly class PropertyReflector implements Reflector, Stringable
{
    use HasAttributes;

    public function __construct(
        private PHPReflectionProperty $reflectionProperty,
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

        if (preg_match('/@var\s+([\\\\\w]+)\[\]/', $doc, $match)) {
            return new TypeReflector($this->resolveDocblockClassName($match[1]));
        }

        if (preg_match('/@var\s+(?:list|array)<([\\\\\w]+)>/', $doc, $match)) {
            return new TypeReflector($this->resolveDocblockClassName($match[1]));
        }

        return null;
    }

    private function resolveDocblockClassName(string $name): string
    {
        if (in_array(
            $name,
            ['array', 'bool', 'callable', 'false', 'float', 'int', 'iterable', 'mixed', 'never', 'null', 'object', 'resource', 'string', 'true', 'void'],
            strict: true,
        )) {
            return $name;
        }

        if (str_starts_with($name, '\\')) {
            return ltrim($name, '\\');
        }

        $segments = explode('\\', $name);
        $firstSegment = $segments[0];
        $aliases = $this->getUseAliases();

        if (isset($aliases[$firstSegment])) {
            return implode('\\', [$aliases[$firstSegment], ...array_slice($segments, 1)]);
        }

        $namespace = $this->getClass()->getReflection()->getNamespaceName();

        if ($namespace === '') {
            return $name;
        }

        return $namespace . '\\' . $name;
    }

    /** @return array<string, string> */
    private function getUseAliases(): array
    {
        $fileName = $this->reflectionProperty->getDeclaringClass()->getFileName();

        if ($fileName === false) {
            return [];
        }

        return $this->collectUseAliases($fileName);
    }

    /** @return array<string, string> */
    private function collectUseAliases(string $fileName): array
    {
        static $cache = [];

        return $cache[$fileName] ??= $this->parseUseStatements($fileName);
    }

    /** @return array<string, string> */
    private function parseUseStatements(string $fileName): array
    {
        // Unreadable files (eval'd code, phar paths) emit a warning we handle as empty aliases.
        $source = @file_get_contents($fileName);

        if ($source === false) {
            return [];
        }

        $tokens = token_get_all($source);

        $aliases = [];
        $depth = 0;
        $count = count($tokens);

        for ($i = 0; $i < $count; $i++) {
            $token = $tokens[$i];

            if (in_array($token, ['{'], true)) {
                $depth++;
                continue;
            }

            if (in_array($token, ['}'], true)) {
                $depth--;
                continue;
            }

            if ($depth !== 0 || ! is_array($token) || $token[0] !== T_USE) {
                continue;
            }

            $next = $tokens[$i + 1] ?? null;

            // Skip `use function ...`, `use const ...` and closure `use (...)`.
            if ($next === '(' || is_array($next) && in_array($next[0], [T_FUNCTION, T_CONST], true)) {
                continue;
            }

            $compact = '';

            for ($j = $i + 1; $j < $count; $j++) {
                $statementToken = $tokens[$j];

                if (in_array($statementToken, [';'], true)) {
                    break;
                }

                if (is_array($statementToken)) {
                    if (in_array($statementToken[0], [T_COMMENT, T_DOC_COMMENT], true)) {
                        continue;
                    }

                    $compact .= $statementToken[1];
                } else {
                    $compact .= $statementToken;
                }
            }

            foreach ($this->parseUseStatement($compact) as $alias => $name) {
                $aliases[$alias] = $name;
            }
        }

        return $aliases;
    }

    /** @return array<string, string> */
    private function parseUseStatement(string $compact): array
    {
        $aliases = [];

        $brace = strpos($compact, '{');
        $prefix = $brace === false ? '' : trim(trim(substr($compact, 0, $brace)), '\\');
        $items = $brace === false ? explode(',', $compact) : explode(',', trim(substr($compact, $brace + 1, -1)));

        foreach ($items as $item) {
            $parts = preg_split('/\bas\b/', trim($item));
            $name = trim($parts[0]);
            $alias = $parts[1] ?? null;

            // Skip `function`/`const` members of grouped imports.
            if ($name === '' || str_starts_with($name, 'function ') || str_starts_with($name, 'const ')) {
                continue;
            }

            if ($alias === null) {
                $nameSegments = explode('\\', $name);
                $alias = array_pop($nameSegments);
            }

            $aliases[trim($alias)] = $prefix === '' ? $name : $prefix . '\\' . $name;
        }

        return $aliases;
    }

    public function isUninitialized(object $object): bool
    {
        return ! $this->reflectionProperty->isInitialized($object);
    }

    public function isVirtual(): bool
    {
        return $this->reflectionProperty->isVirtual();
    }

    public function isHooked(): bool
    {
        return $this->getGetHook() || $this->getSetHook();
    }

    public function getGetHook(): ?MethodReflector
    {
        $hook = $this->reflectionProperty->getHook(PropertyHookType::Get);

        if (! $hook instanceof ReflectionMethod) {
            return null;
        }

        return new MethodReflector($hook);
    }

    public function getSetHook(): ?MethodReflector
    {
        $hook = $this->reflectionProperty->getHook(PropertyHookType::Set);

        if (! $hook instanceof ReflectionMethod) {
            return null;
        }

        return new MethodReflector($hook);
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

        $hasPromotedDefaultValue = $this->isPromoted() && isset($constructorParameters[$this->getName()]) && $constructorParameters[$this->getName()]->isDefaultValueAvailable();

        return $hasDefaultValue || $hasPromotedDefaultValue;
    }

    public function __toString(): string
    {
        return $this->getClass()->getName() . '::' . $this->getName();
    }
}
