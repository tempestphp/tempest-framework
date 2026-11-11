<?php

declare(strict_types=1);

namespace Tempest\Reflection;

use PhpToken;
use ReflectionClass;
use ReflectionProperty;

/** @internal */
final class DocblockContext
{
    /** @return array{namespace: string, aliases: array<string, string>} */
    public static function forProperty(ReflectionProperty $property): array
    {
        $class = $property->getDeclaringClass();
        $name = $property->getName();
        $context = self::forClass($class);

        // A class declaration wins over a compatible property contributed by a trait.
        if (! $property->isPromoted() && ! isset($context['properties'][$name])) {
            foreach ($class->getTraits() as $trait) {
                if ($trait->hasProperty($name)) {
                    return self::forProperty($trait->getProperty($name));
                }
            }
        }

        return ['namespace' => $context['namespace'], 'aliases' => $context['aliases']];
    }

    /**
     * @param ReflectionClass<object> $class
     * @return array{namespace: string, aliases: array<string, string>, properties: array<string, true>}
     */
    private static function forClass(ReflectionClass $class): array
    {
        static $files = [];

        $file = $class->getFileName();
        // shortcut: same-line anonymous classes share metadata; disambiguate if they need different trait origins.
        $key = $class->isAnonymous() ? '@' . $class->getStartLine() : $class->getName();

        if ($file !== false) {
            if (! isset($files[$file][$key])) {
                $source = @file_get_contents($file);

                if ($source !== false) {
                    $context = self::parseHeader($source, $class);

                    if ($context === null) {
                        $files[$file] = self::parseSource($source);
                    } else {
                        $files[$file][$key] = $context;
                    }
                }

                $files[$file][$key] ??= ['namespace' => $class->getNamespaceName(), 'aliases' => [], 'properties' => []];
            }

            return $files[$file][$key];
        }

        return ['namespace' => $class->getNamespaceName(), 'aliases' => [], 'properties' => []];
    }

    /**
     * @param ReflectionClass<object> $class
     * @return array{namespace: string, aliases: array<string, string>, properties: array<string, true>}|null
     */
    private static function parseHeader(string $source, ReflectionClass $class): ?array
    {
        // Only a complete simple header can bypass tokenization; comments and other PHP syntax fall back.
        if ($class->isAnonymous() || preg_match(<<<'REGEX'
        ~\A<\?php\s+
        (?:declare\s*\(\s*strict_types\s*=\s*1\s*\)\s*;\s*)?
        (?:namespace\s+(?<namespace>[\w\x5c]+)\s*;\s*)?
        (?<imports>(?:use\s+[\w\x5c]+(?:\s+as\s+\w+)?\s*;\s*)*)
        (?:(?:final|abstract|readonly)\s+)*class\s+(?<class>\w+)\b
        ~ix
        REGEX, $source, $match) !== 1) {
            return null;
        }

        $namespace = $match['namespace'];

        if (ltrim($namespace . '\\' . $match['class'], '\\') !== $class->getName() || $class->getTraits() !== []) {
            return null;
        }

        preg_match_all('/use\s+([\w\x5c]+)(?:\s+as\s+(\w+))?\s*;/i', $match['imports'], $imports, PREG_SET_ORDER);
        $aliases = [];

        foreach ($imports as $import) {
            $name = ltrim($import[1], '\\');
            $alias = $import[2] ?? substr(strrchr('\\' . $name, '\\'), 1);
            $aliases[strtolower($alias)] = $name;
        }

        return ['namespace' => $namespace, 'aliases' => $aliases, 'properties' => []];
    }

    /** @return array<string, array{namespace: string, aliases: array<string, string>, properties: array<string, true>}> */
    private static function parseSource(string $source): array
    {
        $tokens = array_values(array_filter(PhpToken::tokenize($source), fn (PhpToken $token) => ! $token->isIgnorable()));
        $contexts = [];
        $namespace = '';
        $aliases = [];
        $depth = 0;
        $namespaceDepth = 0;
        $parentheses = 0;
        $classes = [];
        $pendingClasses = [];

        for ($i = 0, $count = count($tokens); $i < $count; $i++) {
            $token = $tokens[$i];
            $symbol = $token->id < 256 ? $token->text : null;

            if ($token->id === T_NAMESPACE && $depth === 0) {
                $namespace = '';
                $aliases = [];

                while (! $tokens[++$i]->is([';', '{'])) {
                    $namespace .= $tokens[$i]->text;
                }

                $namespaceDepth = $tokens[$i]->text === '{' ? ++$depth : 0;
                continue;
            }

            if ($token->id === T_USE && $depth === $namespaceDepth && ($tokens[$i + 1]->text ?? null) !== '(') {
                $aliases = array_replace($aliases, self::parseImport($tokens, $i));
                continue;
            }

            if (in_array($token->id, [T_CLASS, T_TRAIT, T_INTERFACE, T_ENUM], true) && ($tokens[$i - 1]->id ?? null) !== T_DOUBLE_COLON) {
                $name = ($tokens[$i + 1]->id ?? null) === T_STRING ? $tokens[$i + 1]->text : null;
                $key = $name === null ? '@' . $token->line : ltrim($namespace . '\\' . $name, '\\');
                $contexts[$key] = ['namespace' => $namespace, 'aliases' => $aliases, 'properties' => []];
                $pendingClasses[] = ['key' => $key, 'depth' => $depth, 'parentheses' => $parentheses];
            }

            if ($symbol === '{') {
                $pending = end($pendingClasses);

                // Anonymous-class arguments can themselves contain closures or classes.
                if ($pending !== false && $pending['depth'] === $depth && $pending['parentheses'] === $parentheses) {
                    $classes[] = array_pop($pendingClasses);
                }

                $depth++;
            } elseif ($token->id === T_CURLY_OPEN || $token->id === T_DOLLAR_OPEN_CURLY_BRACES) {
                $depth++;
            } elseif ($symbol === '}') {
                $depth--;

                if ($classes !== [] && end($classes)['depth'] === $depth) {
                    array_pop($classes);
                }

                if ($depth < $namespaceDepth) {
                    $namespace = '';
                    $aliases = [];
                    $namespaceDepth = 0;
                }
            } elseif ($symbol === '(') {
                $parentheses++;
            } elseif ($symbol === ')') {
                $parentheses--;
            }

            $class = end($classes);

            if ($class === false || $depth !== ($class['depth'] + 1)) {
                continue;
            }

            if ($token->id === T_VARIABLE && $parentheses === $class['parentheses']) {
                $contexts[$class['key']]['properties'][substr($token->text, 1)] = true;
            }
        }

        return $contexts;
    }

    /**
     * @param list<PhpToken> $tokens
     * @return array<string, string>
     */
    private static function parseImport(array $tokens, int &$i): array
    {
        $aliases = [];
        $prefix = '';
        $name = '';
        $alias = '';
        $aliased = false;
        $statementClassImport = ! $tokens[$i + 1]->is([T_FUNCTION, T_CONST]);
        $classImport = $statementClassImport;

        while (isset($tokens[++$i])) {
            $token = $tokens[$i];

            if ($token->is([T_FUNCTION, T_CONST])) {
                $classImport = false;
            } elseif ($token->id === T_AS) {
                $aliased = true;
            } elseif ($token->text === '{') {
                $prefix = $name;
                $name = '';
            } elseif ($token->is([',', '}', ';'])) {
                if ($classImport && $name !== '') {
                    $fullName = ltrim($prefix . $name, '\\');
                    $alias = $aliased ? $alias : substr(strrchr('\\' . $name, '\\'), 1);
                    $aliases[strtolower($alias)] = $fullName;
                }

                $name = '';
                $alias = '';
                $aliased = false;
                $classImport = $statementClassImport;

                if ($token->text === '}') {
                    $prefix = '';
                } elseif ($token->text === ';') {
                    break;
                }
            } elseif ($aliased) {
                $alias .= $token->text;
            } else {
                $name .= $token->text;
            }
        }

        return $aliases;
    }
}
