<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\ManyToMany;

use PHPUnit\Framework\TestCase;
use Tempest\Database\HasMany;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;

use function Tempest\Database\inspect;
use function Tempest\Database\query;

// Test models without pivot tables (traditional HasMany)
final class Company
{
    use \Tempest\Database\IsDatabaseModel;

    public string $name;

    /** @var Employee[] */
    #[HasMany]
    public array $employees = [];
}

final class Employee
{
    use \Tempest\Database\IsDatabaseModel;

    public string $name;

    public ?string $position = null;

    public ?Company $company = null;
}

final class BackwardCompatibilityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->createTables();
        $this->seedData();
    }

    protected function tearDown(): void
    {
        $this->dropTables();
        parent::tearDown();
    }

    public function test_has_many_without_pivot_still_works(): void
    {
        $companyInspector = inspect(Company::class);
        $employeesRelation = $companyInspector->getHasMany('employees');

        $this->assertInstanceOf(HasMany::class, $employeesRelation);
        $this->assertEquals('employees', $employeesRelation->name);

        // Should not have pivot configuration
        $reflection = new \ReflectionObject($employeesRelation);
        $pivotTableProp = $reflection->getProperty('pivotTable');
        $this->assertNull($pivotTableProp->getValue($employeesRelation));
    }

    public function test_traditional_has_many_loading(): void
    {
        $company = Company::select()
            ->with('employees')
            ->where('name = ?', 'Acme Corp')
            ->first();

        $this->assertNotNull($company);
        $this->assertCount(2, $company->employees);

        $employeeNames = array_map(fn ($emp) => $emp->name, $company->employees);
        $this->assertContains('Alice', $employeeNames);
        $this->assertContains('Bob', $employeeNames);

        // Employees should not have pivot data
        foreach ($company->employees as $employee) {
            $this->assertEmpty($employee->pivot());
        }
    }

    public function test_join_statement_for_traditional_has_many(): void
    {
        $companyInspector = inspect(Company::class);
        $employeesRelation = $companyInspector->getHasMany('employees');
        $employeesRelation->property = $companyInspector->reflector->getProperty('employees');

        $joinStatement = $employeesRelation->getJoinStatement();
        $joinString = $joinStatement->compile(\Tempest\Database\Config\SQLiteDialect::class);

        // Should have single LEFT JOIN (not pivot table joins)
        $this->assertStringContainsString('LEFT JOIN employees', $joinString);
        $this->assertEquals(1, substr_count($joinString, 'LEFT JOIN'));
        $this->assertStringNotContainsString('pivot', $joinString);
    }

    public function test_select_fields_without_pivot(): void
    {
        $companyInspector = inspect(Company::class);
        $employeesRelation = $companyInspector->getHasMany('employees');
        $employeesRelation->property = $companyInspector->reflector->getProperty('employees');

        $selectFields = $employeesRelation->getSelectFields();

        // Should include employee fields but no pivot fields
        $fieldStrings = array_map(
            fn ($field) => $field->compile(\Tempest\Database\Config\SQLiteDialect::class),
            $selectFields->toArray(),
        );

        $this->assertContains('employees.id AS "employees.id"', $fieldStrings);
        $this->assertContains('employees.name AS "employees.name"', $fieldStrings);

        // Should not contain pivot fields
        foreach ($fieldStrings as $field) {
            $this->assertStringNotContainsString('pivot', $field);
        }
    }

    public function test_mixed_relationships_on_same_model(): void
    {
        // Test that a model can have both traditional HasMany and pivot-based relationships
        $testModel = new class {
            use \Tempest\Database\IsDatabaseModel;

            public string $name;

            /** @var Employee[] */
            #[HasMany]
            public array $employees = [];

            /** @var \Tests\Tempest\Fixtures\Models\ManyToMany\Tag[] */
            #[\Tempest\Database\BelongsToMany(pivotFields: ['created_at'])]
            public array $tags = [];
        };

        $inspector = inspect($testModel::class);

        // Traditional HasMany should work
        $employeesRelation = $inspector->getHasMany('employees');
        $this->assertNotNull($employeesRelation);
        $this->assertNull($employeesRelation->pivotTable ?? null);

        // BelongsToMany should work
        $tagsRelation = $inspector->getBelongsToMany('tags');
        $this->assertNotNull($tagsRelation);
    }

    public function test_implicit_has_many_detection_still_works(): void
    {
        // Test model with implicit HasMany (no attribute)
        $testModel = new class {
            use \Tempest\Database\IsDatabaseModel;

            public string $name;

            /** @var Employee[] */
            public array $employees = []; // No explicit attribute
        };

        $inspector = inspect($testModel::class);

        // Should still detect as HasMany
        $relation = $inspector->getHasMany('employees');
        $this->assertNotNull($relation);
        $this->assertInstanceOf(HasMany::class, $relation);
    }

    public function test_belongs_to_still_works(): void
    {
        $employee = Employee::select()
            ->with('company')
            ->where('name = ?', 'Alice')
            ->first();

        $this->assertNotNull($employee);
        $this->assertNotNull($employee->company);
        $this->assertEquals('Acme Corp', $employee->company->name);

        // Company should not have pivot data
        $this->assertEmpty($employee->company->pivot());
    }

    private function createTables(): void
    {
        new CreateTableStatement('companies')
            ->primary()
            ->text('name')
            ->execute();

        new CreateTableStatement('employees')
            ->primary()
            ->text('name')
            ->text('position', nullable: true)
            ->integer('company_id', nullable: true)
            ->execute();
    }

    private function seedData(): void
    {
        $company = Company::create(name: 'Acme Corp');

        query('INSERT INTO employees (name, position, company_id) VALUES (?, ?, ?), (?, ?, ?)')
            ->execute(
                'Alice',
                'Developer',
                $company->id->toInt(),
                'Bob',
                'Manager',
                $company->id->toInt(),
            );
    }

    private function dropTables(): void
    {
        new DropTableStatement('employees')->execute();
        new DropTableStatement('companies')->execute();
    }
}
