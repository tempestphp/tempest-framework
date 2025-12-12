---
title: Mapper
description: "The mapper component is capable of mapping data to objects and the other way around. It is one of Tempest's most powerful tools."
---

## Overview

Tempest comes with a mapper component that can be used to map all sorts of data to objects and back. For instance, it may map the request data to a request class, or the result of an SQL query to a model class.

This component is used internally to handle persistence between models and the database, map PSR objects to internal requests, map request data to objects, and more. It is flexible enough to be used as-is, or you can build your own mappers.

## Mapping data

You may map data from a source to a target using the `map()` function. This function accepts the source data you want to map as its sole parameter, and returns a mapper instance.

Calling the `to()` method on this instance will return a new instance of the target class, populated with the mapped data:

```php
use function Tempest\Mapper\map;

$book = map($rawBookAsJson)->to(Book::class);
```

### Mapping to collections

When the source data is an array, you may instruct the mapper to map each item of the collection to an instance of the target class by calling the `collection()` method.

```php
use function Tempest\Mapper\map;

$books = map($rawBooksAsJson)
    ->collection()
    ->to(Book::class);
```

### Choosing specific mappers

By default, Tempest finds out which mapper to use based on the source and target types. However, you can also specify which mapper to use by calling the `with()` method on the mapper instance. This method accepts one or multiple mapper class names, which will be used for the mapping.

```php
$psrRequest = map($request)
    ->with(RequestToPsrRequestMapper::class)
    ->do();
```

Alternatively, you may also provide closures to the `with()` method. These closures expect the mapper as their first parameter, and the source data as the second. By using closures you get access to the `$from` parameter as well, allowing you to do more advanced mapping via the `with()` method:

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

You may call `toArray()` or `toJson()` on the mapper instance to serialize the mapped data to an array or JSON string, respectively.

```php
$array = map($book)->toArray();
$json = map($book)->toJson();
```

### Overriding field names

When mapping from an array to an object, Tempest will use the property names of the target class to map the data. If a property name doesn't match a key in the source array, you can use the {b`#[Tempest\Mapper\MapFrom]`} attribute to specify the key to map to the property.

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

Similarly, you can use the {b`#[Tempest\Mapper\MapTo]`} attribute to specify the key that will be used when serializing the object to an array or a JSON string.

```php
use Tempest\Mapper\MapTo;

final class Book
{
    #[MapTo('book_title')]
    public string $title;
}
```

### Strict mapping

By default, the mapper allows building objects with missing data. For instance, if you have a class with two properties, and you only provide data for one of them, the mapper will still create an instance of the class.

This is useful for cases where you want to build objects incrementally. Similarly, protected and private properties are ignored and will not be populated.

```php
final class Book
{
    public string $title;
    public string $contents;
}

$book = map(['title' => 'Timeline Taxi'])->to(Book::class); // This is allowed
```

Of course, accessing missing properties after the object has been constructed will result in an uninitialized property error. If you prefer to have the mapper throw an exception when properties are missing, you may mark the class or a specific property with the {`#[Tempest\Mapper\Strict]`} attribute.

```php
use Tempest\Mapper\Strict;

#[Strict]
final class Book
{
    public string $title;
    public string $contents;
}

// Not allowed anymore, MissingValuesException will be thrown
$book = map(['title' => 'Timeline Taxi'])->to(Book::class);
```

## Custom mappers

You may create your own mappers by implementing the {`\Tempest\Mapper\Mapper`} interface. This interface expects a `canMap()` and a `map()` method.

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

Tempest will try its best to find the right mapper for you. All classes that implement the {b`\Tempest\Mapper\Mapper`} interface will be automatically discovered and registered.

Mapper discovery relies on the result of the `canMap()` method. When a mapper is dedicated to mapping a source to a specific class, the `$to` parameter may not necessarily be used.

## Casters and serializers

Casters are responsible for mapping serialized data to a complex type. Similarly, serializers convert complex types to a serialized representation.

You may create your own casters and serializers by implementing the {`\Tempest\Mapper\Caster`} and {`\Tempest\Mapper\Serializer`} interfaces, respectively.

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
use Tempest\Mapper\Serializer;

final readonly class AddressSerializer implements Serializer
{
    public function serialize(mixed $input): array|string
    {
        if (! $input instanceof Address) {
            throw new CannotSerializeValue(Address::class);
        }

        return $input->toArray();
    }
}
```

Of course, Tempest provides casters and serializers for the most common data types, including arrays, booleans, dates, enumerations, integers and value objects.

### Registering casters and serializers globally

You may register casters and serializers globally, so you don't have to specify them for every property. This is useful for value objects that are used frequently. To do so, you may implement the {`\Tempest\Mapper\DynamicCaster`} or {`\Tempest\Mapper\DynamicSerializer`} interface, which require an `accepts` method:

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
            throw new CannotSerializeValue(Address::class);
        }

        return $input->toArray();
    }
}
```

Dynamic serializers and casters will automatically be discovered by Tempest.

### Specifying casters or serializers for properties

You may use a specific caster or serializer for a property by using the {b`#[Tempest\Mapper\CastWith]`} or {b`#[Tempest\Mapper\SerializeWith]`} attribute, respectively.

```php
use Tempest\Mapper\CastWith;

final class User
{
    #[CastWith(AddressCaster::class)]
    public Address $address;
}
```

You may of course use {b`#[Tempest\Mapper\CastWith]`} and {b`#[Tempest\Mapper\SerializeWith]`} together.

## Mapping contexts

Contexts allow you to use different casters, serializers, and mappers depending on the situation. For example, you might want to serialize dates differently for an API response versus database storage, or apply different validation rules for different contexts.

### Using contexts

You may specify a context when mapping by using the `in()` method. Contexts can be provided as a string, an enum, or a {b`\Tempest\Mapper\Context`} object.

```php
use App\SerializationContext;
use function Tempest\Mapper\map;

$json = map($book)
    ->in(SerializationContext::API)
    ->toJson();
```

To create a caster or serializer that only applies in a specific context, use the {b`#[Tempest\Mapper\Attributes\Context]`} attribute on your class:

```php
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

This serializer will only be used when mapping with `->in(SerializationContext::API)`. Without a context specified, or in other contexts, the default serializers will be used.

### Injecting context into casters and serializers

You may inject the current context into your caster or serializer constructor to adapt behavior dynamically. Note that the context property has to be named `$context`. You may also inject any other dependency from the container.

```php
use Tempest\Mapper\Context;
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

Sometimes, a caster or serializer needs to be configured based on the property it's applied to. For example, an enum caster needs to know which enum class to use, or an object caster needs to know the target type.

Implement the {b`\Tempest\Mapper\ConfigurableCaster`} or {b`\Tempest\Mapper\ConfigurableSerializer`} interface to create casters/serializers that are configured per property:

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
        // Create a new instance configured for this specific property
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

The `configure()` method receives the property being mapped and the current context, allowing you to create a caster instance tailored to that specific property.

Note that `ConfigurableSerializer::configure()` can receive either a `PropertyReflector`, `TypeReflector`, or `string`, depending on whether it's being used for property mapping or value serialization.

### When to use configurable casters and serializers

Use configurable casters and serializers when:

- The caster/serializer behavior depends on the specific property type (e.g., enum class, object class)
- You need access to property attributes or metadata
- Different properties of the same base type require different handling
- You want to avoid creating many similar caster/serializer classes

For simple, static behavior that doesn't depend on property information, regular casters and serializers are sufficient.
