<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database;

use Tempest\Database\Builder\WhereOperator;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\MigratesUp;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\DateTime\DateTime;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\query;

/**
 * @internal
 */
final class ConvenientWhereMethodsTest extends FrameworkIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->database->migrate(CreateMigrationsTable::class, CreateUserTable::class);
        $this->seedTestData();
    }

    private function seedTestData(): void
    {
        $users = [
            ['name' => 'Alice', 'email' => 'alice@example.com', 'age' => 25, 'status' => UserStatus::ACTIVE, 'role' => UserRole::USER, 'score' => 85.5],
            ['name' => 'Bob', 'email' => 'bob@example.com', 'age' => 30, 'status' => UserStatus::INACTIVE, 'role' => UserRole::ADMIN, 'score' => 92.0],
            ['name' => 'Charlie', 'email' => 'charlie@example.com', 'age' => 35, 'status' => UserStatus::ACTIVE, 'role' => UserRole::USER, 'score' => 78.3],
            ['name' => 'Diana', 'email' => 'diana@example.com', 'age' => 28, 'status' => UserStatus::PENDING, 'role' => UserRole::MODERATOR, 'score' => 88.7],
            ['name' => 'Eve', 'email' => 'eve@example.com', 'age' => 40, 'status' => UserStatus::ACTIVE, 'role' => UserRole::USER, 'score' => 95.2],
            ['name' => 'Frank', 'email' => null, 'age' => 22, 'status' => UserStatus::INACTIVE, 'role' => UserRole::USER, 'score' => 72.1],
            ['name' => 'Grace', 'email' => 'grace@example.com', 'age' => 33, 'status' => UserStatus::ACTIVE, 'role' => UserRole::ADMIN, 'score' => 89.4],
        ];

        foreach ($users as $userData) {
            query(User::class)
                ->insert(
                    name: $userData['name'],
                    email: $userData['email'],
                    age: $userData['age'],
                    status: $userData['status'],
                    role: $userData['role'],
                    score: $userData['score'],
                    created_at: DateTime::now(),
                )
                ->execute();
        }
    }

    public function test_where_in_with_array(): void
    {
        $users = query(User::class)
            ->select()
            ->whereIn('name', ['Alice', 'Bob', 'Charlie'])
            ->all();

        $this->assertCount(3, $users);

        $names = array_column($users, 'name');
        $this->assertContains('Alice', $names);
        $this->assertContains('Bob', $names);
        $this->assertContains('Charlie', $names);
    }

    public function test_where_in_with_enum_class(): void
    {
        $users = query(User::class)
            ->select()
            ->whereIn('status', UserStatus::class)
            ->all();

        $this->assertCount(7, $users);
    }

    public function test_where_in_with_enum_values(): void
    {
        $users = query(User::class)
            ->select()
            ->whereIn('status', [UserStatus::ACTIVE, UserStatus::PENDING])
            ->all();

        foreach ($users as $user) {
            $this->assertContains($user->status, [UserStatus::ACTIVE, UserStatus::PENDING]);
        }
    }

    public function test_where_not_in(): void
    {
        $users = query(User::class)
            ->select()
            ->whereNotIn('role', [UserRole::ADMIN])
            ->all();

        foreach ($users as $user) {
            $this->assertNotSame(UserRole::ADMIN, $user->role);
        }
    }

    public function test_where_between(): void
    {
        $users = query(User::class)
            ->select()
            ->whereBetween('age', 25, 35)
            ->all();

        foreach ($users as $user) {
            $this->assertGreaterThanOrEqual(25, $user->age);
            $this->assertLessThanOrEqual(35, $user->age);
        }
    }

    public function test_where_not_between(): void
    {
        $users = query(User::class)
            ->select()
            ->whereNotBetween('age', 25, 35)
            ->all();

        foreach ($users as $user) {
            $this->assertTrue($user->age < 25 || $user->age > 35);
        }
    }

    public function test_where_null(): void
    {
        $users = query(User::class)
            ->select()
            ->whereNull('email')
            ->all();

        $this->assertCount(1, $users);
        $this->assertSame('Frank', $users[0]->name);
        $this->assertNull($users[0]->email);
    }

    public function test_where_not_null(): void
    {
        $users = query(User::class)
            ->select()
            ->whereNotNull('email')
            ->all();

        foreach ($users as $user) {
            $this->assertNotNull($user->email);
        }
    }

    public function test_where_not(): void
    {
        $users = query(User::class)
            ->select()
            ->whereNot('status', UserStatus::ACTIVE)
            ->all();

        foreach ($users as $user) {
            $this->assertNotSame(UserStatus::ACTIVE, $user->status);
        }
    }

    public function test_where_like(): void
    {
        $users = query(User::class)
            ->select()
            ->whereLike('email', '%@example.com')
            ->all();

        foreach ($users as $user) {
            $this->assertStringEndsWith('@example.com', $user->email);
        }
    }

    public function test_where_not_like(): void
    {
        $users = query(User::class)
            ->select()
            ->whereNotNull('email')
            ->whereNotLike('email', '%alice%')
            ->all();

        foreach ($users as $user) {
            $this->assertStringNotContainsString('alice', $user->email);
        }
    }

    public function test_or_where_in(): void
    {
        $users = query(User::class)
            ->select()
            ->where('status', UserStatus::PENDING)
            ->orWhereIn('role', [UserRole::ADMIN])
            ->all();

        $this->assertCount(3, $users);

        $names = array_column($users, 'name');
        $this->assertContains('Diana', $names);
        $this->assertContains('Bob', $names);
        $this->assertContains('Grace', $names);
    }

    public function test_or_where_not_in(): void
    {
        $users = query(User::class)
            ->select()
            ->where('name', 'Alice')
            ->orWhereNotIn('status', [UserStatus::ACTIVE, UserStatus::PENDING])
            ->all();

        $this->assertCount(3, $users);

        $names = array_column($users, 'name');
        $this->assertContains('Alice', $names);
        $this->assertContains('Bob', $names);
        $this->assertContains('Frank', $names);
    }

    public function test_or_where_between(): void
    {
        $users = query(User::class)
            ->select()
            ->where('name', 'Frank')
            ->orWhereBetween('age', 35, 40)
            ->all();

        $this->assertCount(3, $users);

        $names = array_column($users, 'name');
        $this->assertContains('Frank', $names);
        $this->assertContains('Charlie', $names);
        $this->assertContains('Eve', $names);
    }

    public function test_or_where_not_between(): void
    {
        $users = query(User::class)
            ->select()
            ->where('name', 'Diana')
            ->orWhereNotBetween('age', 25, 35)
            ->all();

        $this->assertCount(3, $users);

        $names = array_column($users, 'name');
        $this->assertContains('Diana', $names);
        $this->assertContains('Frank', $names);
        $this->assertContains('Eve', $names);
    }

    public function test_or_where_null(): void
    {
        $users = query(User::class)
            ->select()
            ->where('role', UserRole::ADMIN)
            ->orWhereNull('email')
            ->all();

        $this->assertCount(3, $users);

        $names = array_column($users, 'name');
        $this->assertContains('Bob', $names);
        $this->assertContains('Grace', $names);
        $this->assertContains('Frank', $names);
    }

    public function test_or_where_not_null(): void
    {
        $users = query(User::class)
            ->select()
            ->where('age', 22)
            ->orWhereNotNull('email')
            ->all();

        foreach ($users as $user) {
            $this->assertTrue($user->age === 22 || $user->email !== null);
        }
    }

    public function test_or_where_not(): void
    {
        $users = query(User::class)
            ->select()
            ->where('name', 'Alice')
            ->orWhereNot('status', UserStatus::ACTIVE)
            ->all();

        $this->assertCount(4, $users);

        $names = array_column($users, 'name');
        $this->assertContains('Alice', $names);
        $this->assertContains('Bob', $names);
        $this->assertContains('Diana', $names);
        $this->assertContains('Frank', $names);
    }

    public function test_or_where_like(): void
    {
        $users = query(User::class)
            ->select()
            ->where('name', 'Frank')
            ->orWhereLike('email', '%alice%')
            ->all();

        $this->assertCount(2, $users);

        $names = array_column($users, 'name');
        $this->assertContains('Frank', $names);
        $this->assertContains('Alice', $names);
    }

    public function test_or_where_not_like(): void
    {
        $users = query(User::class)
            ->select()
            ->where('age', 22)
            ->orWhereNotLike('email', '%example.com')
            ->all();

        $this->assertCount(1, $users);
        $this->assertSame('Frank', $users[0]->name);
    }

    public function test_complex_where_combination(): void
    {
        $users = query(User::class)
            ->select()
            ->whereIn('status', [UserStatus::ACTIVE, UserStatus::PENDING])
            ->whereBetween('age', 25, 35)
            ->whereNotNull('email')
            ->all();

        foreach ($users as $user) {
            $this->assertContains($user->status, [UserStatus::ACTIVE, UserStatus::PENDING]);
            $this->assertGreaterThanOrEqual(25, $user->age);
            $this->assertLessThanOrEqual(35, $user->age);
            $this->assertNotNull($user->email);
        }
    }

    public function test_chaining_or_conditions(): void
    {
        $users = query(User::class)
            ->select()
            ->where('age', 22)
            ->orWhereIn('role', [UserRole::ADMIN])
            ->orWhereNull('email')
            ->all();

        $this->assertCount(3, $users);

        $names = array_column($users, 'name');
        $this->assertContains('Frank', $names);
        $this->assertContains('Bob', $names);
        $this->assertContains('Grace', $names);
    }

    public function test_where_in_throws_exception_for_invalid_value(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('IN operator requires an array of values');

        query(User::class)
            ->select()
            ->whereIn('name', 'not-an-array') // @phpstan-ignore argument.type
            ->all();
    }

    public function test_where_between_throws_exception_for_invalid_array(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('BETWEEN operator requires an array with exactly 2 values');

        query(User::class)
            ->select()
            ->whereField('age', [25], WhereOperator::BETWEEN)
            ->all();
    }

    public function test_where_between_throws_exception_for_too_many_values(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('BETWEEN operator requires an array with exactly 2 values');

        query(User::class)
            ->select()
            ->whereField('age', [25, 30, 35], WhereOperator::BETWEEN)
            ->all();
    }
}

final class CreateUserTable implements MigratesUp
{
    private(set) string $name = '0000-00-20_create_users_table';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(User::class)
            ->primary()
            ->text('name')
            ->text('email', nullable: true)
            ->integer('age')
            ->text('status')
            ->text('role')
            ->float('score')
            ->datetime('created_at');
    }
}

final class User
{
    use IsDatabaseModel;

    public function __construct(
        public string $name,
        public ?string $email,
        public int $age,
        public UserStatus $status,
        public UserRole $role,
        public float $score,
        public DateTime $created_at,
    ) {}
}

enum UserStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case PENDING = 'pending';
}

enum UserRole: string
{
    case USER = 'user';
    case ADMIN = 'admin';
    case MODERATOR = 'moderator';
}
