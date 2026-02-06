---
title: Mapper
description: "The mapper component is capable of mapping data to objects and the other way around. It is one of Tempest's most powerful tools."
---

## Overview

Tempest provides a mapper component for mapping data to objects and back. The component maps request data to request classes, SQL query results to model classes, and other data transformations.

This component is used internally for persistence between models and the database, it maps PSR objects to internal requests, request data to objects, and more.

## Mapping data

To map data from a source to a target, use the {b`\Tempest\Mapper\map()`} function. This function accepts the source data as its sole parameter and returns a mapper instance.

Calling the `to()` method on this instance returns a new instance of the target class, populated with the mapped data:

```php
use function Tempest\Mapper\map;

$book = map($rawBookAsJson)->to(Book::class);
```

### Mapping to collections

When the source data is an array, calling the `collection()` method instructs the mapper to map each item to an instance of the target class.

```php
use function Tempest\Mapper\map;

$books = map($rawBooksAsJson)
    ->collection()
    ->to(Book::class);
```

### Choosing specific mappers

By default, Tempest determines which mapper to use based on the source and target types. To specify which mapper to use explicitly, call the `with()` method on the mapper instance. This method accepts one or multiple mapper class names to use for the mapping.

```php
$psrRequest = map($request)
    ->with(RequestToPsrRequestMapper::class)
    ->do();
```

Alternatively, provide closures to the `with()` method. These closures expect the mapper as their first parameter and the source data as the second. Using closures provides access to the `$from` parameter for more advanced mapping operations:

```php
$result = map($rawBooksAsJson)
    ->with(fn (ArrayToBooksMapper $mapper, array $books) => $mapper->map($books, Book::class))
    ->do();
```

Of course, `with()` can also be combined with `collection()` and `to()`.

```php
use function Tempest\Mapper\map;

$books = map($rawBooksAsJson)
    ->collection()
    ->with(ArrayToBooksMapper::class)
    ->to(Book::class);
```

### Serializing to arrays or JSON

To serialize the mapped data to an array or JSON string, call `toArray()` or `toJson()` on the mapper instance, respectively.

```php
$array = map($book)->toArray();
$json = map($book)->toJson();
```

### Overriding field names

When mapping from an array to an object, Tempest uses the property names of the target class to map the data. If a property name doesn't match a key in the source array, use the {b`#[Tempest\Mapper\MapFrom]`} attribute to specify the source key to map to the property.

```php
use Tempest\Mapper\MapFrom;

final class Book
{
    #[MapFrom('book_title')]
    public string $title;
}
```

In the following example, the `book_title` key from the source array will be mapped to the `title` property of the `Book` class.

```php
$book = map(['book_title' => 'Timeline Taxi'])->to(Book::class);
```

Similarly, use the {b`#[Tempest\Mapper\MapTo]`} attribute to specify the key used when serializing the object to an array or a JSON string.

```php
use Tempest\Mapper\MapTo;

final class Book
{
    #[MapTo('book_title')]
    public string $title;
}
```

### Strict mapping

By default, the mapper allows building objects with missing data. For instance, if a class has two properties and data is provided for only one, the mapper still creates an instance of the class.

This behavior supports building objects incrementally. Protected and private properties are ignored and not populated.

```php
final class Book
{
    public string $title;
    public string $contents;
}

// Allowed
$book = map(['title' => 'Timeline Taxi'])->to(Book::class);
```

Accessing missing properties after the object has been constructed results in an uninitialized property error. To have the mapper throw an exception when properties are missing, mark the class or a specific property with the {b`#[Tempest\Mapper\Strict]`} attribute.

```php
use Tempest\Mapper\Strict;

use function Tempest\Mapper\map;

#[Strict]
final class Book
{
    public string $title;
    public string $contents;
}

// MappingValuesWereMissing is thrown
$book = map(['title' => 'Timeline Taxi'])->to(Book::class);
```

## Custom mappers

To create custom mappers, implement the {`\Tempest\Mapper\Mapper`} interface. This interface requires a `canMap()` and a `map()` method.

```php
final readonly class PsrRequestToRequestMapper implements Mapper
{
    public function canMap(mixed $from, mixed $to): bool
    {
        if (! $from instanceof PsrRequest) {
            return false;
        }

        return is_a($to, Request::class, allow_string: true);
    }

    public function map(mixed $from, mixed $to): object
    { /* … */ }
}
```

### Mapper discovery

Tempest automatically discovers and registers all classes that implement the {b`\Tempest\Mapper\Mapper`} interface.

Mapper discovery relies on the result of the `canMap()` method. When a mapper is dedicated to mapping a source to a specific class, the `$to` parameter is not necessarily used.

## Casters and serializers

Casters map serialized data to a complex type. Serializers convert complex types to a serialized representation.

To create custom casters and serializers, implement the {`\Tempest\Mapper\Caster`} and {`\Tempest\Mapper\Serializer`} interfaces, respectively.

:::code-group

```php app/AddressCaster.php
use Tempest\Mapper\Caster;

final readonly class AddressCaster implements Caster
{
    public function cast(mixed $input): Address
    {
        return new Address(
            street: $input['street'],
            city: $input['city'],
            postalCode: $input['postal_code'],
        );
    }
}
```

```php app/AddressSerializer.php
use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;
use Tempest\Mapper\Serializer;

final readonly class AddressSerializer implements Serializer
{
    public function serialize(mixed $input): array|string
    {
        if (! $input instanceof Address) {
            throw new ValueCouldNotBeSerialized(Address::class);
        }

        return $input->toArray();
    }
}
```

:::

Of course, Tempest provides casters and serializers for the most common data types, including arrays, booleans, dates, enumerations, integers and value objects.

### Registering casters and serializers globally

To register casters and serializers globally without specifying them for every property, implement the {b`\Tempest\Mapper\DynamicCaster`} or {b`\Tempest\Mapper\DynamicSerializer`} interface, which require an `accepts` method:

```php app/AddressSerializer.php
use Tempest\Mapper\Serializer;
use Tempest\Mapper\DynamicSerializer;

final readonly class AddressSerializer implements Serializer, DynamicSerializer
{
    public static function accepts(PropertyReflector|TypeReflector $input): bool
    {
        $type = $input instanceof PropertyReflector
            ? $input->getType()
            : $input;

        return $type->matches(Address::class);
    }

    public function serialize(mixed $input): array|string
    {
        if (! $input instanceof Address) {
            throw new ValueCouldNotBeSerialized(Address::class);
        }

        return $input->toArray();
    }
}
```

:::info
Dynamic serializers and casters will automatically be discovered by Tempest.
:::

### Specifying casters or serializers for properties

To use a specific caster or serializer for a property, apply the {b`#[Tempest\Mapper\CastWith]`} or {b`#[Tempest\Mapper\SerializeWith]`} attribute, respectively. Of course, both attributes can be used together on the same property.

```php
use Tempest\Mapper\CastWith;

final class User
{
    #[CastWith(AddressCaster::class)]
    #[SerializeWith(AddressSerializer::class)]
    public Address $address;
}
```

## Mapping contexts

Contexts enable using different casters, serializers, and mappers depending on the situation. For example, dates can be serialized differently for an API response versus database storage, or different validation rules can be applied for different contexts.

### Specifying a context

To specify a context when mapping, use the `in()` method on the mapper instance. Contexts can be provided as a string, an enum, or a {b`\Tempest\Mapper\Context`} object.

```php
use App\SerializationContext;
use function Tempest\Mapper\map;

$json = map($book)
    ->in(SerializationContext::API)
    ->toJson();
```

To create a caster or serializer that only applies in a specific context, use the {b`#[Tempest\Mapper\Attributes\Context]`} attribute on your class and provide it with a context name:

```php app/ApiDateSerializer.php
use Tempest\DateTime\DateTime;
use Tempest\DateTime\FormatPattern;
use Tempest\Mapper\Attributes\Context;
use Tempest\Mapper\Serializer;
use Tempest\Mapper\DynamicSerializer;

#[Context(SerializationContext::API)]
final readonly class ApiDateSerializer implements Serializer, DynamicSerializer
{
    public static function accepts(PropertyReflector|TypeReflector $input): bool
    {
        $type = $input instanceof PropertyReflector
            ? $input->getType()
            : $input;

        return $type->matches(DateTime::class);
    }

    public function serialize(mixed $input): string
    {
        return $input->format(FormatPattern::ISO8601);
    }
}
```

This serializer is only used when mapping with `->in(SerializationContext::API)`. Without a context specified, or in other contexts, the default serializers are used.

### Injecting context into casters and serializers

To adapt behavior dynamically, inject the current context into the caster or serializer constructor by naming its property `$context`. Other dependencies from the container can also be injected.

```php
use Tempest\Mapper\Attributes\Context;
use Tempest\Mapper\Serializer;

#[Context(DatabaseContext::class)]
final class BooleanSerializer implements Serializer, DynamicSerializer
{
    public function __construct(
        private DatabaseContext $context,
    ) {}

    public static function accepts(PropertyReflector|TypeReflector $type): bool
    {
        $type = $type instanceof PropertyReflector
            ? $type->getType()
            : $type;

        return $type->getName() === 'bool' || $type->getName() === 'boolean';
    }

    public function serialize(mixed $input): string
    {
        return match ($this->context->dialect) {
            DatabaseDialect::POSTGRESQL => $input ? 'true' : 'false',
            default => $input ? '1' : '0',
        };
    }
}
```

## Configurable casters and serializers

Casters or serializers sometimes need configuration based on the property they're applied to. For example, an enum caster needs to know which enum class to use, and an object caster needs to know the target type.

To create casters or serializers that are configured per property, implement the {b`\Tempest\Mapper\ConfigurableCaster`} or {b`\Tempest\Mapper\ConfigurableSerializer`} interface:

```php
use Tempest\Mapper\Caster;
use Tempest\Mapper\ConfigurableCaster;
use Tempest\Mapper\Context;
use Tempest\Mapper\DynamicCaster;
use Tempest\Reflection\PropertyReflector;

final readonly class EnumCaster implements Caster, DynamicCaster, ConfigurableCaster
{
    /**
     * @param class-string<UnitEnum> $enum
     */
    public function __construct(
        private string $enum,
    ) {}

    public static function accepts(PropertyReflector|TypeReflector $input): bool
    {
        $type = $input instanceof PropertyReflector
            ? $input->getType()
            : $input;

        return $type->matches(UnitEnum::class);
    }

    public static function configure(PropertyReflector $property, Context $context): self
    {
        // Create a new instance configured for this property
        return new self(enum: $property->getType()->getName());
    }

    public function cast(mixed $input): ?object
    {
        if ($input === null) {
            return null;
        }

        // Use the configured enum class
        return $this->enum::from($input);
    }
}
```

The `configure()` method receives the property being mapped and the current context, enabling the creation of a caster instance tailored to that specific property.

Note that `ConfigurableSerializer::configure()` can receive either a `PropertyReflector`, `TypeReflector`, or `string`, depending on whether it's used for property mapping or value serialization.

Configurable casters and serializers are appropriate when:

- The caster or serializer behavior depends on the specific property type (e.g., enum class, object class),
- Access to property attributes or metadata is required,
- Different properties of the same base type require different handling,
- Creating many similar caster or serializer classes needs to be avoided.

For static behavior that doesn't depend on property information, regular casters and serializers are sufficient.
