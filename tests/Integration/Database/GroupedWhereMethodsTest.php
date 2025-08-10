<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database;

use Tempest\Database\Builder\WhereOperator;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\MigratesUp;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\PrimaryKey;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;
use Tempest\DateTime\DateTime;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\query;

/**
 * @internal
 */
final class GroupedWhereMethodsTest extends FrameworkIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->migrate(CreateMigrationsTable::class, CreateProductTable::class);
        $this->seedTestData();
    }

    private function seedTestData(): void
    {
        $products = [
            ['name' => 'Laptop', 'category' => 'electronics', 'price' => 999.99, 'in_stock' => true, 'rating' => 4.5, 'brand' => 'TechCorp'],
            ['name' => 'Mouse', 'category' => 'electronics', 'price' => 29.99, 'in_stock' => true, 'rating' => 4.2, 'brand' => 'TechCorp'],
            ['name' => 'Keyboard', 'category' => 'electronics', 'price' => 79.99, 'in_stock' => false, 'rating' => 4.3, 'brand' => 'TypeMaster'],
            ['name' => 'Chair', 'category' => 'furniture', 'price' => 199.99, 'in_stock' => true, 'rating' => 4.1, 'brand' => 'ComfortZone'],
            ['name' => 'Desk', 'category' => 'furniture', 'price' => 299.99, 'in_stock' => false, 'rating' => 4.0, 'brand' => 'ComfortZone'],
            ['name' => 'Monitor', 'category' => 'electronics', 'price' => 249.99, 'in_stock' => true, 'rating' => 4.4, 'brand' => 'ViewPro'],
            ['name' => 'Lamp', 'category' => 'furniture', 'price' => 49.99, 'in_stock' => true, 'rating' => 3.8, 'brand' => 'LightUp'],
            ['name' => 'Phone', 'category' => 'electronics', 'price' => 699.99, 'in_stock' => false, 'rating' => 4.6, 'brand' => 'MobileTech'],
        ];

        foreach ($products as $productData) {
            query(Product::class)->insert(
                name: $productData['name'],
                category: $productData['category'],
                price: $productData['price'],
                in_stock: $productData['in_stock'],
                rating: $productData['rating'],
                brand: $productData['brand'],
                created_at: DateTime::now(),
            )->execute();
        }
    }

    public function test_simple_where_group(): void
    {
        $products = query(Product::class)
            ->select()
            ->whereGroup(function ($query): void {
                $query
                    ->where('category', 'electronics')
                    ->where('in_stock', true);
            })
            ->all();

        foreach ($products as $product) {
            $this->assertSame('electronics', $product->category);
            $this->assertTrue($product->in_stock);
        }
    }

    public function test_and_where_group(): void
    {
        $products = query(Product::class)
            ->select()
            ->where('category', 'electronics')
            ->andWhereGroup(function ($query): void {
                $query
                    ->whereField('price', 100.0, WhereOperator::GREATER_THAN)
                    ->where('in_stock', true);
            })
            ->all();

        foreach ($products as $product) {
            $this->assertSame('electronics', $product->category);
            $this->assertGreaterThan(100.0, $product->price);
            $this->assertTrue($product->in_stock);
        }
    }

    public function test_or_where_group(): void
    {
        $products = query(Product::class)
            ->select()
            ->where('category', 'furniture')
            ->orWhereGroup(function ($query): void {
                $query
                    ->whereField('price', 500.0, WhereOperator::GREATER_THAN)
                    ->where('brand', 'TechCorp');
            })
            ->all();

        $this->assertCount(4, $products); // All furniture (Chair, Desk, Lamp) + Laptop (>500 + TechCorp)

        $furnitureCount = 0;
        $expensiveTechCorpCount = 0;

        foreach ($products as $product) {
            if ($product->category === 'furniture') {
                $furnitureCount++;
            } elseif ($product->price > 500.0 && $product->brand === 'TechCorp') {
                $expensiveTechCorpCount++;
            }
        }

        $this->assertSame(3, $furnitureCount);
        $this->assertSame(1, $expensiveTechCorpCount);
    }

    public function test_nested_where_groups(): void
    {
        $products = query(Product::class)
            ->select()
            ->whereGroup(function ($query): void {
                $query
                    ->where('category', 'electronics')
                    ->orWhereGroup(function ($subQuery): void {
                        $subQuery
                            ->where('category', 'furniture')
                            ->whereField('price', 200.0, WhereOperator::LESS_THAN);
                    });
            })
            ->where('in_stock', true)
            ->all();

        $this->assertCount(5, $products);

        foreach ($products as $product) {
            $this->assertTrue($product->in_stock);
            $this->assertTrue($product->category === 'electronics' || $product->category === 'furniture' && $product->price < 200.0);
        }
    }

    public function test_complex_grouped_conditions(): void
    {
        $products = query(Product::class)
            ->select()
            ->whereGroup(function ($query): void {
                $query
                    ->where('brand', 'TechCorp')
                    ->orWhere('brand', 'ViewPro');
            })
            ->andWhereGroup(function ($query): void {
                $query
                    ->where('rating', 4.0, WhereOperator::GREATER_THAN_OR_EQUAL)
                    ->where('price', 300.0, WhereOperator::LESS_THAN);
            })
            ->all();

        $this->assertCount(2, $products);

        foreach ($products as $product) {
            $this->assertContains($product->brand, ['TechCorp', 'ViewPro']);
            $this->assertGreaterThanOrEqual(4.0, $product->rating);
            $this->assertLessThan(300.0, $product->price);
        }
    }

    public function test_where_group_with_convenient_methods(): void
    {
        $products = query(Product::class)
            ->select()
            ->whereGroup(function ($query): void {
                $query
                    ->whereIn('category', ['electronics', 'furniture'])
                    ->whereBetween('price', 50.0, 250.0)
                    ->whereNotNull('brand');
            })
            ->all();

        foreach ($products as $product) {
            $this->assertContains($product->category, ['electronics', 'furniture']);
            $this->assertGreaterThanOrEqual(50.0, $product->price);
            $this->assertLessThanOrEqual(250.0, $product->price);
            $this->assertNotNull($product->brand);
        }
    }

    public function test_where_group_with_raw_conditions(): void
    {
        $products = query(Product::class)
            ->select()
            ->whereGroup(function ($query): void {
                $query
                    ->whereRaw('price > ?', 100.0)
                    ->andWhereRaw('rating >= ?', 4.0);
            })
            ->all();

        foreach ($products as $product) {
            $this->assertGreaterThan(100.0, $product->price);
            $this->assertGreaterThanOrEqual(4.0, $product->rating);
        }
    }

    public function test_where_group_with_or_raw_conditions(): void
    {
        $products = query(Product::class)
            ->select()
            ->whereGroup(function ($query): void {
                $query
                    ->where('brand', 'TechCorp')
                    ->orWhereRaw('rating > ?', 4.5);
            })
            ->all();

        $techCorpCount = 0;
        $highRatingCount = 0;

        foreach ($products as $product) {
            if ($product->brand === 'TechCorp') {
                $techCorpCount++;
            } elseif ($product->rating > 4.5) {
                $highRatingCount++;
            }
        }

        $this->assertGreaterThanOrEqual(2, $techCorpCount);
        $this->assertGreaterThanOrEqual(1, $highRatingCount);
    }

    public function test_empty_where_group_is_ignored(): void
    {
        $products = query(Product::class)
            ->select()
            ->where('category', 'electronics')
            ->whereGroup(function (): void {})
            ->all();

        foreach ($products as $product) {
            $this->assertSame('electronics', $product->category);
        }
    }

    public function test_multiple_where_groups(): void
    {
        $products = query(Product::class)
            ->select()
            ->whereGroup(function ($query): void {
                $query
                    ->where('category', 'electronics')
                    ->orWhere('category', 'furniture');
            })
            ->andWhereGroup(function ($query): void {
                $query
                    ->where('in_stock', true)
                    ->orWhere('rating', 4.5, WhereOperator::GREATER_THAN);
            })
            ->all();

        foreach ($products as $product) {
            $this->assertContains($product->category, ['electronics', 'furniture']);
            $this->assertTrue($product->in_stock || $product->rating > 4.5);
        }
    }

    public function test_where_group_with_all_logical_operators(): void
    {
        $products = query(Product::class)
            ->select()
            ->whereGroup(function ($query): void {
                $query
                    ->where('brand', 'TechCorp')
                    ->andWhere('category', 'electronics')
                    ->orWhere('price', 300.0, WhereOperator::GREATER_THAN);
            })
            ->all();

        $this->assertGreaterThanOrEqual(3, $products);

        $techCorpElectronicsCount = 0;
        $expensiveCount = 0;

        foreach ($products as $product) {
            if ($product->brand === 'TechCorp' && $product->category === 'electronics') {
                $techCorpElectronicsCount++;
            } elseif ($product->price > 300.0) {
                $expensiveCount++;
            }
        }

        $this->assertGreaterThanOrEqual(2, $techCorpElectronicsCount);
        $this->assertGreaterThanOrEqual(1, $expensiveCount);
    }

    public function test_deeply_nested_where_groups(): void
    {
        $products = query(Product::class)
            ->select()
            ->whereGroup(function ($query): void {
                $query
                    ->where('category', 'electronics')
                    ->orWhereGroup(function ($subQuery): void {
                        $subQuery
                            ->where('category', 'furniture')
                            ->andWhereGroup(function ($deepQuery): void {
                                $deepQuery
                                    ->whereField('price', 150.0, WhereOperator::GREATER_THAN)
                                    ->orWhere('brand', 'LightUp');
                            });
                    });
            })
            ->all();

        $this->assertGreaterThanOrEqual(6, $products);

        foreach ($products as $product) {
            $isValid = $product->category === 'electronics' || $product->category === 'furniture' && ($product->price > 150.0 || $product->brand === 'LightUp');
            $this->assertTrue($isValid);
        }
    }
}

final class CreateProductTable implements MigratesUp
{
    private(set) string $name = '0000-00-30_create_products_table';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(Product::class)
            ->primary()
            ->text('name')
            ->text('category')
            ->float('price')
            ->boolean('in_stock')
            ->float('rating')
            ->text('brand')
            ->datetime('created_at');
    }
}

final class Product
{
    use IsDatabaseModel;

    public function __construct(
        public string $name,
        public string $category,
        public float $price,
        public bool $in_stock,
        public float $rating,
        public string $brand,
        public DateTime $created_at,
    ) {}
}
