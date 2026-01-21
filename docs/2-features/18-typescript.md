---
title: TypeScript
description: "Tempest provides the ability to generate TypeScript interfaces from PHP classes to ease integration with TypeScript-based front-ends."
keywords: ["Experimental", "Generation"]
experimental: true
---

## Overview

When building applications with TypeScript-based front-ends like [Inertia](https://inertiajs.com), keeping your client-side types synchronized with your PHP backend can be tedious and error-prone.

Tempest solves this by automatically generating TypeScript definitions from your PHP value objects, data transfer objects, and enums.

You can choose to output a single `.d.ts` declaration file or a directory tree of individual `.ts` modules, depending on your project's needs.

## Generating types

Mark any PHP class with the {b`#[Tempest\Generation\TypeScript\AsType]`} attribute to instruct Tempest that a matching TypeScript interface must be generated based on its public properties.

By default, all application enums are also included automatically without needing an attribute. Generate your TypeScript definitions by running `generate:typescript-types`:

```sh ">_ generate:typescript-types"
✓ // Generated 14 type definitions across 2 namespaces.
```

This command scans your marked classes, generates the corresponding TypeScript definitions, and writes them to your configured output location.

## Customizing type resolution

Tempest provides several built-in type resolvers for common types: strings, numbers, dates, enums and class references.

You can add your own resolver by providing implementations of {b`Tempest\Generation\TypeScript\TypeResolvers\TypeResolver`}. This interface requires a `canResolve()` method to determine if the resolver can handle a given type, and a `resolve()` method to perform the actual resolution.

The following is the actual implementation of the built-in resolver that handles scalar types:

```php ScalarTypeResolver.php
#[Priority(Priority::LOW)]
final class ScalarTypeResolver implements TypeResolver
{
    public function canResolve(TypeReflector $type): bool
    {
        return $type->isBuiltIn()
            && in_array($type->getName(), ['string', 'int', 'float', 'bool'], strict: true);
    }

    public function resolve(TypeReflector $type, TypeScriptGenerator $generator): ResolvedType
    {
        return new ResolvedType(match ($type->getName()) {
            'string' => 'string',
            'int', 'float' => 'number',
            'bool' => 'boolean',
        });
    }
}
```

:::info
Type resolvers are automatically [discovered](../1-essentials/05-discovery.md) and do not need to be registered manually.
:::

## Configuring output location

By default, Tempest generates a `types.d.ts` definition file at the root of the project, in which the generated types are organized by namespace.

This may be configured by creating a `typescript.config.php` [configuration file](../1-essentials/06-configuration.md#configuration-files) and returning one of the available configuration objects.

### Single file output

To keep all of the TypeScript definitions in a single `.d.ts` declaration file, which is the default, return a {b`Tempest\Generation\TypeScript\Writers\NamespacedTypeScriptGenerationConfig`} object and specify the desired output filename.

```php
use Tempest\Generation\TypeScript\Writers\NamespacedTypeScriptGenerationConfig;

return new NamespacedTypeScriptGenerationConfig(
    filename: 'types.d.ts',
);
```

The declaration file should be automatically picked up by TypeScript—if not, ensure that it's included in the `include` property of your `tsconfig.json`:

```json
{
	"include": ["types.d.ts"]
}
```

You may then reference the generated types globally by using their namespaces:

```ts
defineProps<{
	entry: Module.Changelog.ChangelogEntry
}>()
```

### Directory structure output

If you prefer to mirror your PHP namespace structure in separate files, you may return a {b`Tempest\Generation\TypeScript\Writers\DirectoryTypeScriptGenerationConfig`} configuration object:

```php
use Tempest\Generation\TypeScript\Writers\DirectoryTypeScriptGenerationConfig;

return new DirectoryTypeScriptGenerationConfig(
    directory: 'src/Web/types',
);
```

This creates a directory tree of individual `.ts` files, making it easier to navigate your types. Each namespace gets its own file, and imports between files are handled automatically.
