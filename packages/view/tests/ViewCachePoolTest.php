<?php

declare(strict_types=1);

namespace Tempest\View\Tests;

use Exception;
use PHPUnit\Framework\TestCase;
use Tempest\View\ViewCachePool;

use function Tempest\Support\path;

/**
 * @internal
 */
final class ViewCachePoolTest extends TestCase
{
    private const string DIRECTORY = __DIR__ . '/.cache';

    private ViewCachePool $pool;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pool = new ViewCachePool(
            directory: self::DIRECTORY,
        );
    }

    protected function tearDown(): void
    {
        $directory = path(self::DIRECTORY);

        if ($directory->isDirectory()) {
            /** @phpstan-ignore-next-line */
            $directory->glob('/*.php')->each(fn (string $file) => unlink($file));

            rmdir(self::DIRECTORY);
        }

        parent::tearDown();
    }

    public function test_get_item(): void
    {
        $item = $this->pool->getItem('test');
        $item->set('hi');

        $this->pool->save($item);

        $this->assertFileExists(path(self::DIRECTORY, 'test.php')->toString());
        $this->assertEquals('hi', file_get_contents(path(self::DIRECTORY, 'test.php')->toString()));
    }

    public function test_has_item(): void
    {
        $item = $this->pool->getItem('test');
        $item->set('hi');

        $this->pool->save($item);

        $this->assertTrue($this->pool->hasItem('test'));
        $this->assertFalse($this->pool->hasItem('test-1'));
    }

    public function test_get_items(): void
    {
        $items = $this->pool->getItems(['a', 'b']);

        $this->assertCount(2, $items);

        $this->assertFalse($items[0]->isHit());
        $this->assertFalse($items[1]->isHit());

        $items[0]->set('hi');
        $this->pool->save($items[0]);

        $items = $this->pool->getItems(['a', 'b']);

        $this->assertTrue($items[0]->isHit());
        $this->assertFalse($items[1]->isHit());
    }

    public function test_delete_item(): void
    {
        $item = $this->pool->getItem('test');
        $item->set('hi');

        $this->pool->save($item);
        $this->pool->deleteItem('test');

        $this->assertFileDoesNotExist(path(self::DIRECTORY, 'test.php')->toString());
    }

    public function test_delete_items(): void
    {
        $items = $this->pool->getItems(['a', 'b']);

        $items[0]->set('hi');
        $this->pool->save($items[0]);

        $items[1]->set('hi');
        $this->pool->save($items[1]);

        $this->assertFileExists(path(self::DIRECTORY, 'a.php')->toString());
        $this->assertFileExists(path(self::DIRECTORY, 'b.php')->toString());

        $this->pool->deleteItems(['a', 'b']);

        $this->assertFileDoesNotExist(path(self::DIRECTORY, 'a.php')->toString());
        $this->assertFileDoesNotExist(path(self::DIRECTORY, 'b.php')->toString());
    }

    public function test_clear_pool(): void
    {
        $item = $this->pool->getItem('test');
        $item->set('hi');

        $this->pool->save($item);
        $this->pool->clear();

        $this->assertFileDoesNotExist(path(self::DIRECTORY, 'test.php')->toString());
        $this->assertDirectoryDoesNotExist(path(self::DIRECTORY)->toString());
    }

    public function test_save_deferred(): void
    {
        $this->expectException(Exception::class);

        $this->pool->saveDeferred($this->pool->getItem('test'));
    }

    public function test_commit(): void
    {
        $this->expectException(Exception::class);

        $this->pool->commit();
    }
}
