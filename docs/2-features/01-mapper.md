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
use function Tempest\map;

$book = map($rawBookAsJson)->to(Book::class);
```

### Mapping to collections

When the source data is an array, you may instruct the mapper to map each item of the collection to an instance of the target class by calling the `collection()` method.

```php
use function Tempest\map;

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
use function Tempest\map;

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
use Tempest\Mapper\MapFrom;

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

### Registering casters and serializers globally

You may register casters and serializers globally, so you don't have to specify them for every property. This is useful for value objects that are used frequently.

```php
use Tempest\Mapper\Casters\CasterFactory;
use Tempest\Mapper\Serializers\SerializerFactory;

// Register a caster globally for a specific type
$container->get(CasterFactory::class)
	->addCaster(Address::class, AddressCaster::class);

// Register a serializer globally for a specific type
$container->get(SerializerFactory::class)
	->addSerializer(Address::class, AddressSerializer::class);
```

If you're looking for the right place where to put this logic, [provider classes](/docs/extra-topics/package-development#provider-classes) is our recommendation. 
