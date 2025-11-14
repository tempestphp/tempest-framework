<?php

namespace Tempest\Support\Tests\Filesystem;

use PHPUnit\Framework\Attributes\PostCondition;
use PHPUnit\Framework\Attributes\PreCondition;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Tempest\Support\Filesystem;
use Tempest\Support\Filesystem\Exceptions\NameWasInvalid;
use Tempest\Support\Filesystem\Exceptions\PathWasNotADirectory;
use Tempest\Support\Filesystem\Exceptions\PathWasNotAFile;
use Tempest\Support\Filesystem\Exceptions\PathWasNotASymbolicLink;
use Tempest\Support\Filesystem\Exceptions\PathWasNotFound;
use Tempest\Support\Filesystem\Exceptions\PathWasNotReadable;
use Tempest\Support\Filesystem\Exceptions\RuntimeException;

final class UnixFunctionsTest extends TestCase
{
    private string $fixtures = __DIR__ . '/Fixtures';

    #[PreCondition]
    protected function configure(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('This test is only for Unix-like systems.');
        }

        Filesystem\ensure_directory_empty($this->fixtures);

        $this->assertTrue(is_dir($this->fixtures));
    }

    #[PostCondition]
    protected function cleanup(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            return;
        }

        Filesystem\delete_directory($this->fixtures);

        $this->assertFalse(is_dir($this->fixtures));
    }

    public function test_create_directory(): void
    {
        $directory = $this->fixtures . '/tmp';

        Filesystem\create_directory($directory);

        $this->assertTrue(is_dir($directory));
    }

    public function test_create_directory_when_file_exists(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/^Failed to create directory.*/');

        $file = $this->fixtures . '/file.txt';

        file_put_contents($file, '');

        Filesystem\create_directory($file);
    }

    public function test_create_directory_for_file(): void
    {
        $file = $this->fixtures . '/tmp/file.txt';

        Filesystem\create_directory_for_file($file);

        $this->assertTrue(is_dir(dirname($file)));
    }

    public function test_create_file(): void
    {
        $file = $this->fixtures . '/tmp/file.txt';

        Filesystem\create_file($file);

        $this->assertTrue(is_file($file));
    }

    public function test_exists(): void
    {
        $dir = $this->fixtures . '/tmp';
        $file = $this->fixtures . '/tmp/file.txt';

        mkdir($dir);
        file_put_contents($file, '');

        $this->assertTrue(Filesystem\exists($dir));
        $this->assertTrue(Filesystem\exists($file));
    }

    public function test_delete(): void
    {
        $dir = $this->fixtures . '/tmp';
        $file = $this->fixtures . '/tmp/file.txt';

        mkdir($dir);
        file_put_contents($file, '');

        Filesystem\delete($file);
        $this->assertFalse(is_file($file));

        Filesystem\delete($dir);
        $this->assertFalse(is_dir($dir));

        // should not throw
        Filesystem\delete($dir . '/non-existent-path');
    }

    public function test_delete_directory(): void
    {
        $dir = $this->fixtures . '/tmp';

        mkdir($dir);

        Filesystem\delete_directory($dir);

        $this->assertFalse(is_dir($dir));
    }

    public function test_delete_directory_on_file(): void
    {
        $this->expectException(PathWasNotADirectory::class);

        $file = $this->fixtures . '/file.txt';

        file_put_contents($file, '');

        Filesystem\delete_directory($file);
    }

    public function test_delete_directory_recursive(): void
    {
        $dir = $this->fixtures . '/tmp';

        mkdir($dir);
        file_put_contents($dir . '/file.txt', '');
        mkdir($dir . '/sub');
        file_put_contents($dir . '/sub/file.txt', '');

        Filesystem\delete_directory($dir);

        $this->assertFalse(is_dir($dir));
    }

    public function test_delete_directory_non_recursive(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/.*Directory not empty.*/');

        $dir = $this->fixtures . '/tmp';

        mkdir($dir);
        file_put_contents($dir . '/file.txt', '');
        mkdir($dir . '/sub');
        file_put_contents($dir . '/sub/file.txt', '');

        Filesystem\delete_directory($dir, recursive: false);
    }

    public function test_detele_file(): void
    {
        $file = $this->fixtures . '/file.txt';

        file_put_contents($file, '');

        Filesystem\delete_file($file);

        $this->assertFalse(is_file($file));
    }

    public function test_detele_file_not_found(): void
    {
        $this->expectException(PathWasNotFound::class);

        $file = $this->fixtures . '/file.txt';

        Filesystem\delete_file($file);
    }

    public function test_detele_file_on_dir(): void
    {
        $this->expectException(PathWasNotAFile::class);

        $dir = $this->fixtures . '/tmp';
        mkdir($dir);

        Filesystem\delete_file($dir);
    }

    public function test_get_permissions(): void
    {
        $file = $this->fixtures . '/file.txt';

        file_put_contents($file, '');

        $permissions = Filesystem\get_permissions($file);

        $this->assertEquals(0o644, $permissions & 0o777);
    }

    public function test_get_permissions_not_found(): void
    {
        $this->expectException(PathWasNotFound::class);

        Filesystem\get_permissions($this->fixtures . '/file.txt');
    }

    public function test_ensure_directory_empty(): void
    {
        $dir = $this->fixtures . '/tmp';

        mkdir($dir);
        file_put_contents($dir . '/file.txt', '');

        Filesystem\ensure_directory_empty($dir);

        $this->assertFalse(is_file($dir . '/file.txt'));
        $this->assertTrue(is_dir($dir));
    }

    public function test_ensure_directory_empty_on_file(): void
    {
        $this->expectException(PathWasNotADirectory::class);

        $file = $this->fixtures . '/file.txt';

        file_put_contents($file, '');

        Filesystem\ensure_directory_empty($file);
    }

    public function test_ensure_directory_empty_keeps_permissions(): void
    {
        $dir = $this->fixtures . '/tmp';

        mkdir($dir, 0o755);
        file_put_contents($dir . '/file.txt', '');

        Filesystem\ensure_directory_empty($dir);

        $permissions = Filesystem\get_permissions($dir);

        $this->assertEquals(0o755, $permissions & 0o777);
    }

    public function test_is_file(): void
    {
        $file = $this->fixtures . '/file.txt';

        file_put_contents($file, '');

        $this->assertTrue(Filesystem\is_file($file));
        $this->assertFalse(Filesystem\is_file($this->fixtures));
    }

    public function test_is_directory(): void
    {
        $dir = $this->fixtures . '/tmp';

        mkdir($dir);

        $this->assertTrue(Filesystem\is_directory($dir));
        $this->assertFalse(Filesystem\is_directory($this->fixtures . '/file.txt'));
    }

    public function test_is_readable(): void
    {
        $file = $this->fixtures . '/file.txt';

        file_put_contents($file, '');

        $this->assertTrue(Filesystem\is_readable($file));
        $this->assertTrue(Filesystem\is_readable($this->fixtures));
    }

    public function test_is_symbolic_link(): void
    {
        $file = $this->fixtures . '/file.txt';
        $link = $this->fixtures . '/link.txt';

        file_put_contents($file, '');
        symlink($file, $link);

        $this->assertTrue(Filesystem\is_symbolic_link($link));
        $this->assertFalse(Filesystem\is_symbolic_link($file));
    }

    public function test_is_writable(): void
    {
        $file = $this->fixtures . '/file.txt';

        file_put_contents($file, '');

        $this->assertTrue(Filesystem\is_writable($file));
        $this->assertTrue(Filesystem\is_writable($this->fixtures));
    }

    public function test_list_directory(): void
    {
        $dir = $this->fixtures . '/tmp';

        mkdir($dir);
        file_put_contents($dir . '/file.txt', '');
        mkdir($dir . '/sub');
        file_put_contents($dir . '/sub/file.txt', '');

        $files = Filesystem\list_directory($dir);

        $this->assertCount(2, $files);

        if (PHP_OS_FAMILY !== 'Windows') {
            $this->assertContains(realpath($dir . '/file.txt'), $files);
            $this->assertContains(realpath($dir . '/sub'), $files);
        }
    }

    public function test_list_directory_on_non_directory(): void
    {
        $this->expectException(PathWasNotADirectory::class);

        $file = $this->fixtures . '/file.txt';

        file_put_contents($file, '');

        Filesystem\list_directory($file);
    }

    public function test_read_symbolic_link(): void
    {
        $file = $this->fixtures . '/file.txt';
        $link = $this->fixtures . '/link.txt';

        file_put_contents($file, '');
        symlink($file, $link);

        $target = Filesystem\read_symbolic_link($link);

        $this->assertEquals(realpath($file), $target);
    }

    public function test_read_symbolic_link_on_non_symlink(): void
    {
        $this->expectException(PathWasNotASymbolicLink::class);

        $file = $this->fixtures . '/file.txt';

        file_put_contents($file, '');

        Filesystem\read_symbolic_link($file);
    }

    public function test_get_directory(): void
    {
        $file = $this->fixtures . '/file.txt';

        file_put_contents($file, '');

        $directory = Filesystem\get_directory($file);

        $this->assertEquals(realpath($this->fixtures), realpath($directory));
    }

    public function test_copy(): void
    {
        $source = $this->fixtures . '/file.txt';
        $destination = $this->fixtures . '/tmp/file.txt';

        file_put_contents($source, '');

        Filesystem\copy_file($source, $destination);

        $this->assertTrue(is_file($destination));
    }

    public function test_copy_directory(): void
    {
        $this->expectException(PathWasNotAFile::class);

        $source = $this->fixtures . '/tmp';
        $destination = $this->fixtures . '/tmp2';

        mkdir($source);
        file_put_contents($source . '/file.txt', '');

        Filesystem\copy_file($source, $destination);
    }

    public function test_copy_non_existing_file(): void
    {
        $this->expectException(PathWasNotFound::class);

        $source = $this->fixtures . '/file.txt';
        $destination = $this->fixtures . '/tmp/file.txt';

        Filesystem\copy_file($source, $destination);
    }

    public function test_copy_non_readable_file(): void
    {
        $this->expectException(PathWasNotReadable::class);

        $source = $this->fixtures . '/file.txt';
        $destination = $this->fixtures . '/tmp/file.txt';

        file_put_contents($source, '');
        chmod($source, 0o000);

        Filesystem\copy_file($source, $destination);
    }

    public function test_copy_overwrite(): void
    {
        $source = $this->fixtures . '/file.txt';
        $destination = $this->fixtures . '/file2.txt';

        file_put_contents($source, 'Hello');
        file_put_contents($destination, 'World');

        Filesystem\copy_file($source, $destination, overwrite: true);

        $this->assertEquals('Hello', file_get_contents($destination));
    }

    public function test_move(): void
    {
        $source = $this->fixtures . '/file.txt';
        $destination = $this->fixtures . '/tmp/file.txt';

        file_put_contents($source, '');

        Filesystem\move($source, $destination);

        $this->assertTrue(is_file($destination));
        $this->assertFalse(is_file($source));
    }

    public function test_move_overwrite(): void
    {
        $source = $this->fixtures . '/file.txt';
        $destination = $this->fixtures . '/tmp/file.txt';

        Filesystem\write_file($source, 'Hello');
        Filesystem\write_file($destination, 'World');

        Filesystem\move($source, $destination, overwrite: true);

        $this->assertEquals('Hello', file_get_contents($destination));
        $this->assertFalse(is_file($source));
    }

    public function test_move_no_overwrite(): void
    {
        $source = $this->fixtures . '/file.txt';
        $destination = $this->fixtures . '/tmp/file.txt';

        Filesystem\write_file($source, 'Hello');
        Filesystem\write_file($destination, 'World');

        Filesystem\move($source, $destination, overwrite: false);

        $this->assertEquals('Hello', file_get_contents($source));
        $this->assertEquals('World', file_get_contents($destination));
    }

    public function test_move_directory(): void
    {
        $source = $this->fixtures . '/tmp';
        $destination = $this->fixtures . '/tmp2';

        mkdir($source);
        file_put_contents($source . '/file.txt', 'hello');

        Filesystem\move($source, $destination);

        $this->assertTrue(is_dir($destination));
        $this->assertSame('hello', file_get_contents($destination . '/file.txt'));
        $this->assertFalse(is_dir($source));
    }

    public function test_move_directory_overwrite(): void
    {
        $source = $this->fixtures . '/tmp';
        $destination = $this->fixtures . '/tmp2';

        Filesystem\create_directory($source);
        Filesystem\write_file($source . '/file.txt', 'hello');
        Filesystem\create_directory($destination);
        Filesystem\write_file($destination . '/file.txt', 'hello');

        Filesystem\move($source, $destination, overwrite: true);

        $this->assertTrue(is_dir($destination));
        $this->assertSame('hello', file_get_contents($destination . '/file.txt'));
        $this->assertFalse(is_dir($source));
    }

    public function test_move_directory_no_overwrite(): void
    {
        $source = $this->fixtures . '/tmp';
        $destination = $this->fixtures . '/tmp2';

        Filesystem\create_directory($source);
        Filesystem\write_file($source . '/file.txt', 'hello');
        Filesystem\create_directory($destination);
        Filesystem\write_file($destination . '/file.txt', 'world');

        Filesystem\move($source, $destination, overwrite: false);

        $this->assertTrue(is_dir($source));
        $this->assertSame('hello', file_get_contents($source . '/file.txt'));
        $this->assertTrue(is_dir($destination));
        $this->assertSame('world', file_get_contents($destination . '/file.txt'));
    }

    public function test_move_not_readable(): void
    {
        $this->expectException(PathWasNotReadable::class);

        $source = $this->fixtures . '/file.txt';
        $destination = $this->fixtures . '/tmp/file.txt';

        file_put_contents($source, '');
        chmod($source, 0o000);

        Filesystem\move($source, $destination);
    }

    public function test_rename(): void
    {
        $source = $this->fixtures . '/file.txt';
        $newName = 'renamed-file.txt';

        Filesystem\write_file($source, '');

        Filesystem\rename($source, $newName);

        $this->assertTrue(is_file($this->fixtures . '/renamed-file.txt'));
        $this->assertFalse(is_file($source));
    }

    public function test_rename_overwrite(): void
    {
        $source = $this->fixtures . '/file.txt';
        $newName = 'renamed-file.txt';

        Filesystem\write_file($source, 'Hello');
        Filesystem\write_file($this->fixtures . '/' . $newName, 'World');

        Filesystem\rename($source, $newName, overwrite: true);

        $this->assertEquals('Hello', file_get_contents($this->fixtures . '/' . $newName));
        $this->assertFalse(is_file($source));
    }

    public function test_rename_with_full_path(): void
    {
        $this->expectException(NameWasInvalid::class);

        $source = $this->fixtures . '/file.txt';
        $newName = $this->fixtures . '/renamed-file.txt';

        Filesystem\write_file($source, '');

        Filesystem\rename($source, $newName);

        $this->assertTrue(is_file($newName));
        $this->assertFalse(is_file($source));
    }

    public function test_write_file(): void
    {
        $file = $this->fixtures . '/tmp/file.txt';

        Filesystem\write_file($file, 'Hello');

        $this->assertEquals('Hello', file_get_contents($file));
    }

    #[TestWith([['key' => 'value'], '{"key":"value"}'])]
    #[TestWith(['basic string', '"basic string"'])]
    #[TestWith(['{"foo": "bar"}', '{"foo":"bar"}'])]
    public function test_write_json(mixed $data, string $expected): void
    {
        $file = $this->fixtures . '/tmp/file.json';

        Filesystem\write_json($file, $data, pretty: false);

        $this->assertEquals($expected, file_get_contents($file));
    }

    public function test_write_json_serializable(): void
    {
        $file = $this->fixtures . '/tmp/file.json';

        $data = new class implements \JsonSerializable {
            public function jsonSerialize(): array
            {
                return ['key' => 'value'];
            }
        };

        Filesystem\write_json($file, $data, pretty: false);

        $this->assertEquals('{"key":"value"}', file_get_contents($file));
    }

    public function test_write_non_writable_file(): void
    {
        $this->expectException(RuntimeException::class);

        $file = $this->fixtures . '/file.txt';

        file_put_contents($file, '');
        chmod($file, 0o000);

        Filesystem\write_file($file, 'Hello');
    }

    public function test_read_json(): void
    {
        $file = $this->fixtures . '/tmp/file.json';

        Filesystem\write_json($file, ['key' => 'value']);

        $data = Filesystem\read_json($file);

        $this->assertEquals(['key' => 'value'], $data);
    }

    public function test_read_file(): void
    {
        $file = $this->fixtures . '/file.txt';

        file_put_contents($file, 'Hello');

        $content = Filesystem\read_file($file);

        $this->assertEquals('Hello', $content);
    }

    public function test_read_file_non_readable_file(): void
    {
        $this->expectException(PathWasNotReadable::class);

        $file = $this->fixtures . '/file.txt';

        file_put_contents($file, 'Hello');
        chmod($file, 0o000);

        Filesystem\read_file($file);
    }

    public function test_read_file_not_found(): void
    {
        $this->expectException(PathWasNotFound::class);

        $file = $this->fixtures . '/file.txt';

        Filesystem\read_file($file);
    }

    public function test_ensure_directory_exists(): void
    {
        $dir = $this->fixtures . '/tmp';

        Filesystem\ensure_directory_exists($dir);

        $this->assertTrue(is_dir($dir));
    }

    public function test_ensure_directory_exists_on_existent_directory(): void
    {
        $dir = $this->fixtures . '/tmp';

        mkdir($dir);

        Filesystem\ensure_directory_exists($dir);

        $this->assertTrue(is_dir($dir));
    }

    public function test_delete_file_for_invalid_symlink(): void
    {
        $file = $this->fixtures . '/file.txt';
        \file_put_contents($file, 'hello');
        $link = $this->fixtures . '/link.txt';
        symlink($file, $link);
        unlink($file);

        Filesystem\delete_file($link);

        $this->assertFalse(is_file($link));
        $this->assertFalse(is_link($link));
    }

    public function test_normalize_path_in_phar(): void
    {
        if (\Phar::canWrite() === false) {
            $this->markTestSkipped('phar.readonly is enabled in php.ini.');
        }

        $pharFile = $this->fixtures . '/phar.phar';
        $phar = new \Phar($pharFile, 0, 'phar.phar');
        $phar->addFile(__DIR__ . '/../../src/Filesystem/functions.php', 'functions.php');
        $phar->addFile(__DIR__ . '/../Fixtures/Phar/normalize_path.php', 'index.php');
        $phar->createDefaultStub('index.php');

        $output = shell_exec('php ' . $pharFile);

        $this->assertIsString($output);
        $this->assertStringStartsWith('phar://', $output);
    }
}
