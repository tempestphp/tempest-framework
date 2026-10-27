<?php

declare(strict_types=1);

namespace Tempest\Generation\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Generation\Php\DataObjects\StubFile;
use Tempest\Generation\Php\StubFileType;
use Tempest\Generation\Tests\Php\Fixtures\ClassFromFile;

/**
 * @internal
 */
final class StubFileTest extends TestCase
{
    private string $path;

    protected function setUp(): void
    {
        $this->path = tempnam(sys_get_temp_dir(), 'stub');
    }

    protected function tearDown(): void
    {
        unlink($this->path);
    }

    #[Test]
    public function resolves_class_names_to_their_file(): void
    {
        $stub = StubFile::from(ClassFromFile::class);

        $this->assertSame(StubFileType::CLASS_FILE, $stub->type);
        $this->assertSame(realpath(__DIR__ . '/Fixtures/ClassFromFile.php'), $stub->filePath);
    }

    #[Test]
    #[DataProvider('files')]
    public function detects_the_type_of_files(string $contents, StubFileType $type): void
    {
        file_put_contents($this->path, $contents);

        $this->assertSame($type, StubFile::from($this->path)->type);
    }

    public static function files(): iterable
    {
        yield 'class' => ["<?php\n\nfinal class Example {}\n", StubFileType::CLASS_FILE];
        yield 'config' => ["<?php\n\nreturn new class {};\n", StubFileType::RAW_FILE];
        yield 'anonymous class with attribute' => ["<?php\n\nreturn new #[SomeAttribute] class {};\n", StubFileType::RAW_FILE];
        yield 'conditional class' => ["<?php\n\nif (true) {\n    class Example {}\n}\n", StubFileType::RAW_FILE];
        yield 'class in function' => ["<?php\n\nfunction example(): void\n{\n    class Example {}\n}\n", StubFileType::RAW_FILE];
        yield 'class in alternative syntax' => ["<?php\n\nif (true):\n    class Example {}\nendif;\n", StubFileType::RAW_FILE];
        yield 'class after a brace in a string' => ["<?php\n\n\$value = \"\$name}\";\n\nclass Example {}\n", StubFileType::CLASS_FILE];
        yield 'class with a comment before its name' => ["<?php\n\nclass /** comment */ Example {}\n", StubFileType::CLASS_FILE];
        yield 'class in braced namespace' => ["<?php\n\nnamespace App {\n    final class Example {}\n}\n", StubFileType::CLASS_FILE];
        yield 'view' => ["<div>{{ \$title }}</div>\n", StubFileType::RAW_FILE];
        yield 'invalid php' => ["<?php\n\nclass {\n", StubFileType::RAW_FILE];
    }

    #[Test]
    public function throws_for_missing_files(): void
    {
        $this->expectException(InvalidArgumentException::class);

        StubFile::from(__DIR__ . '/missing.php');
    }
}
