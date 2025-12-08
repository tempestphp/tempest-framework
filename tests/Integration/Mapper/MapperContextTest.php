<?php

namespace Tests\Tempest\Integration\Mapper;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Core\Priority;
use Tempest\Mapper;
use Tests\Tempest\Fixtures\Modules\Books\Models\Author;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class MapperContextTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function fallbacks_to_default_context(): void
    {
        $author = Mapper\make(Author::class)
            ->in('not-a-context')
            ->from([
                'id' => 1,
                'name' => 'test',
            ]);

        $this->assertSame('test', $author->name);
        $this->assertSame(1, $author->id->value);
    }

    #[Test]
    public function uses_serializers_from_given_context(): void
    {
        $author = new Author(
            name: 'test',
        );

        $factory = $this->container->get(Mapper\SerializerFactory::class);
        $factory->addSerializer('string', CustomStringSerializer::class, context: 'custom', priority: Priority::HIGHEST);

        $serialized = Mapper\map($author)
            ->in('custom')
            ->toJson();

        $this->assertSame('{"name":"{\"type\":\"string\",\"value\":\"test\"}","type":"a","books":[],"publisher":null,"id":null}', $serialized);
    }

    #[Test]
    public function uses_casters_from_given_context(): void
    {
        $factory = $this->container->get(Mapper\CasterFactory::class);
        $factory->addCaster('string', CustomStringSerializer::class, context: 'custom', priority: Priority::HIGHEST);

        $author = Mapper\make(Author::class)
            ->in('custom')
            ->from([
                'id' => 1,
                'name' => 'test',
            ]);

        $this->assertInstanceOf(Author::class, $author);
        $this->assertSame('test', $author->name);
        $this->assertSame(1, $author->id->value);
    }
}

final readonly class CustomStringSerializer implements Mapper\Serializer, Mapper\Caster
{
    public static function for(): string
    {
        return 'string';
    }

    public function serialize(mixed $input): string
    {
        return json_encode(['type' => 'string', 'value' => $input]);
    }

    public function cast(mixed $input): mixed
    {
        $data = json_decode($input, associative: true);

        return $data['value'] ?? null;
    }
}
