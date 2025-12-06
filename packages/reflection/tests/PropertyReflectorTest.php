<?php

declare(strict_types=1);

namespace Tempest\Reflection\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Reflection\PropertyReflector;
use Tempest\Reflection\Tests\Fixtures\IterableTypeResolution\Author;
use Tempest\Reflection\Tests\Fixtures\IterableTypeResolution\Book;
use Tempest\Reflection\Tests\Fixtures\IterableTypeResolution\Models\AliasedUseStatement;
use Tempest\Reflection\Tests\Fixtures\IterableTypeResolution\Models\FullyQualifiedClassName;
use Tempest\Reflection\Tests\Fixtures\IterableTypeResolution\Models\GroupUseStatement;
use Tempest\Reflection\Tests\Fixtures\IterableTypeResolution\Models\RegularUseStatement;
use Tempest\Reflection\Tests\Fixtures\IterableTypeResolution\SameNamespace;

final class PropertyReflectorTest extends TestCase
{
    #[Test]
    public function iterable_type_with_regular_use_statement(): void
    {
        $property = PropertyReflector::fromParts(RegularUseStatement::class, 'books');

        $iterableType = $property->getIterableType();

        $this->assertNotNull($iterableType);
        $this->assertSame(Book::class, $iterableType->getName());
    }

    #[Test]
    public function iterable_type_with_aliased_use_statement(): void
    {
        $property = PropertyReflector::fromParts(AliasedUseStatement::class, 'books');

        $iterableType = $property->getIterableType();

        $this->assertNotNull($iterableType);
        $this->assertSame(Book::class, $iterableType->getName());
    }

    #[Test]
    public function iterable_type_with_group_use_statement(): void
    {
        $booksProperty = PropertyReflector::fromParts(GroupUseStatement::class, 'books');
        $authorsProperty = PropertyReflector::fromParts(GroupUseStatement::class, 'authors');

        $booksType = $booksProperty->getIterableType();
        $authorsType = $authorsProperty->getIterableType();

        $this->assertNotNull($booksType);
        $this->assertSame(Book::class, $booksType->getName());

        $this->assertNotNull($authorsType);
        $this->assertSame(Author::class, $authorsType->getName());
    }

    #[Test]
    public function iterable_type_with_same_namespace(): void
    {
        $property = PropertyReflector::fromParts(SameNamespace::class, 'books');

        $iterableType = $property->getIterableType();

        $this->assertNotNull($iterableType);
        $this->assertSame(Book::class, $iterableType->getName());
    }

    #[Test]
    public function iterable_type_with_fully_qualified_class_name(): void
    {
        $property = PropertyReflector::fromParts(FullyQualifiedClassName::class, 'books');

        $iterableType = $property->getIterableType();

        $this->assertNotNull($iterableType);
        $this->assertSame(Book::class, $iterableType->getName());
    }
}
