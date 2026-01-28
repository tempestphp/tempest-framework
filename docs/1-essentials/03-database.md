---
title: Database
description: "Tempest's database component provides data persistence to SQLite, MySQL, and PostgreSQL databases through a query builder and decoupled model architecture."
keywords: ["experimental", "orm", "database", "sqlite", "postgresql", "pgsql", "mysql", "query", "sql", "connection", "models"]
---

:::warning
Tempest's database component is currently experimental and is not covered by our backwards compatibility promise.
:::

## Connecting to a database

By default, Tempest connects to a local SQLite database located in its internal storage, `.tempest/database.sqlite`. The default database connection can be overridden by creating a [configuration file](../1-essentials/06-configuration.md#configuration-files):

```php app/Config/database.config.php
use Tempest\Database\Config\SQLiteConfig;
use function Tempest\root_path;

return new SQLiteConfig(
    path: root_path('database.sqlite'),
);
```

Alternatively, connect to another database by returning a different configuration object from the file. Available configuration classes include {b`Tempest\Database\Config\SQLiteConfig`}, {b`Tempest\Database\Config\MysqlConfig`}, and {b`Tempest\Database\Config\PostgresConfig`}:

```php app/Config/database.config.php
use Tempest\Database\Config\PostgresConfig;
use function Tempest\env;

return new PostgresConfig(
    host: env('DATABASE_HOST', default: '127.0.0.1'),
    port: env('DATABASE_PORT', default: '5432'),
    username: env('DATABASE_USERNAME', default: 'postgres'),
    password: env('DATABASE_PASSWORD', default: 'postgres'),
    database: env('DATABASE_DATABASE', default: 'postgres'),
);
```

## Querying the database

Multiple approaches exist for querying the database, all of which execute a {b`Tempest\Database\Query`} on the {b`Tempest\Database\Database`} class. The most straightforward approach is to inject {b`Tempest\Database\Database`}:

```php
use Tempest\Database\Database;
use Tempest\Database\Query;

final class BookRepository
{
    public function __construct(
        private readonly Database $database,
    ) {}

    public function findById(int $id): array
    {
        return $this->database->fetchFirst(new Query(
            sql: 'SELECT id, title FROM books WHERE id = ?',
            bindings: [$id],
        ));
    }
}
```

Manually building and executing queries provides maximum flexibility. Tempest's query builder offers a more convenient approach with fluent methods that abstract database-specific syntax differences.

```php
use function Tempest\Database\query;

final class BookRepository
{
    public function findById(int $id): array
    {
        return query('books')
            ->select('id', 'title')
            ->where('id', $id)
            ->first();
    }
}
```

Both methods can be combined by using the query builder to construct a query that is then executed on a database:

```php
use Tempest\Database\Database;
use function Tempest\Database\query;

final class BookRepository
{
    public function __construct(
        private readonly Database $database,
    ) {}

    public function findById(int $id): array
    {
        return $this->database->fetchFirst(
            query('books')
                ->select('id', 'title')
                ->where('id = ?', $id),
        );
    }
}
```

## Models

A common use case in many applications is to represent persisted data as objects within the codebase. Model classes fulfill this purpose. Tempest decouples models from the database as much as possible, allowing any object with public typed properties to represent a table.

These objects do not require implementing any interface—they can be plain PHP objects:

```php app/Book.php
use Tempest\Validation\Rules\HasLength;
use App\Author;

final class Book
{
    #[HasLength(min: 1, max: 120)]
    public string $title;

    public ?Author $author = null;

    /** @var \App\Chapter[] */
    public array $chapters = [];
}
```

Because model objects are not tied specifically to the database, Tempest's [mapper](../2-features/01-mapper.md) can map data from many different sources to them. For instance, models can be persisted as JSON:

```php
use function Tempest\Mapper\map;

$books = map($json)->collection()->to(Book::class); // from JSON source to Book collection
$json = map($books)->toJson(); // from Book collection to JSON
```

### Models and query builders

The query builder provides a straightforward approach to persisting models to a database. It can work with tables and arrays as well as map data to and from model objects. Specify the class to query, and Tempest handles the mapping.

The following example selects all fields from the table related to the `Book` model, loads the related `chapters` and `author`, filters by the book ID, and returns the first result:

```php
use App\Models\Book;
use function Tempest\Database\query;

final class BookRepository
{
    public function findById(int $id): Book
    {
        return query(Book::class)
            ->select()
            ->with('chapters', 'author')
            ->where('id', $id)
            ->first();
    }
}
```

Tempest infers all relation-type information from the model class by analyzing property types. For example, a property with the `Author` type is assumed to be a "belongs to" relation, while a property with the `/** @var \App\Books\Chapter[] */` docblock is assumed to be a "has many" relation on the `Chapter` model.

Beyond selecting models, any query builder can be used with model objects:

```php
use App\Models\Book;
use Tempest\Database\PrimaryKey;

;use function Tempest\Database\query;

final class BookRepository
{
    public function create(Book $book): PrimaryKey
    {
        return query(Book::class)
            ->insert($book)
            ->execute();
    }
}
```

### Model relations

Tempest infers relations based on type information from the model class. A public property with a reference to another class is assumed to be a {b`Tempest\Database\BelongsTo`} relation, while a property with a docblock that defines an array type is assumed to be a {b`Tempest\Database\HasMany`} relation.

```php
use App\Author;

final class Book
{
    public ?Author $author = null;
    //      ^ BelongsTo relation

    /** @var \App\Books\Chapter[] */
    public array $chapters = [];
    //     ^ HasMany relation
}
```

:::warning
Due to a restriction with reflection, relation types in docblocks must always be fully qualified. Short class names are not supported.
:::

### Relation attributes

Tempest infers all information needed to build queries. When property names and type information do not map one-to-one to the database schema, dedicated attributes can be used to define relations.

Available attributes are {b`#[Tempest\Database\HasMany]`}, {b`#[Tempest\Database\HasOne]`}, and {b`#[Tempest\Database\BelongsTo]`}. They accept two arguments:

- `ownerJoin`, which is used to build the owner's side of join query,
- `relationJoin`, which is used to build the relation's side of the join query.

```php
use Tempest\Database\BelongsTo;
use Tempest\Database\HasMany;
use Tempest\Database\HasOne;

final class Book
{
    #[BelongsTo(ownerJoin: 'books.author_uuid', relationJoin: 'authors.uuid')]
    public ?Author $author = null;

    /** @var \App\Chapter[] */
    #[HasMany(ownerJoin: 'chapters.book_uuid', relationJoin: 'books.uuid')]
    public array $chapters = [];

    #[HasOne(ownerJoin: 'isbns.book_uuid', relationJoin: 'books.uuid')]
    public ?Isbn $isbn = null;
}
```

The _owner_ part of the relation represents the table that _owns_ the relation—the table with a column referencing another table. The _relation_ part represents the table that is _being referenced by another table_.

The {b`Tempest\Database\BelongsTo`} relation starts with _the owner join_, while both {b`Tempest\Database\HasMany`} and {b`Tempest\Database\HasOne`} start with _the relation join_.

The full owner or relation join does not need to include both the table and field names. Field names can be specified without the table name, in which case the table name is inferred from the related model:

```php
use Tempest\Database\BelongsTo;
use Tempest\Database\HasMany;
use Tempest\Database\HasOne;

final class Book
{
    #[BelongsTo(ownerJoin: 'author_uuid', relationJoin: 'uuid')]
    public ?Author $author = null;

    /** @var \App\Chapter[] */
    #[HasMany(ownerJoin: 'chapter_uuid', relationJoin: 'uuid')]
    public array $chapters = [];

    #[HasOne(ownerJoin: 'book_uuid', relationJoin: 'uuid')]
    public ?Isbn $isbn = null;
}
```

### Using UUIDs as primary keys

By default, Tempest uses auto-incrementing integers as primary keys. UUIDs can be used as primary keys instead by annotating the {b`Tempest\Database\PrimaryKey`} property with the {b`#[Tempest\Database\Uuid]`} attribute. Tempest automatically generates a UUID v7 when a new model is created:

```php app/Books/Book.php
use Tempest\Database\PrimaryKey;
use Tempest\Database\Uuid;

final class Book
{
    #[Uuid]
    public PrimaryKey $uuid;

    public function __construct(
        public string $title,
        public string $author_name,
    ) {}
}
```

Within migrations, specify `uuid: true` to the `primary()` method, or use `uuid()` directly:

```php app/Books/CreateBooksTable.php
use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;

final class CreateBooksTable implements MigratesUp
{
    public string $name = '2024-08-12_create_books_table';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('books')
            ->primary('uuid', uuid: true)
            ->text('title')
            ->text('author_name');
    }
}
```

:::warning
Currently, the [`IsDatabaseModel`](#the-is-database-model-trait) trait already provides a primary `$id` property. It is therefore not possible to use UUIDs alongside `IsDatabaseModel`.
:::

### Table names

Tempest infers the table name for a model class based on the model's classname. By default, the table name is the pluralized, `snake_cased` version of the base class name. This can be overridden using the {b`Tempest\Database\Table`} attribute:

```php
use Tempest\Database\Table;

#[Table('table_books')]
final class Book
{
    // …
}
```

It is possible to define your own convention for naming tables without specifying the {b`Tempest\Database\Table`} attribute on all your models. To do so, set the `namingStrategy` parameter of your database configuration to a {b`Tempest\Database\Tables\NamingStrategy`} instance.

By default, Tempest provides a {b`Tempest\Database\Tables\PascalCaseStrategy`} and {b`Tempest\Database\Tables\PluralizedSnakeCaseStrategy`} strategy, the latter being the default. Of course, custom strategies can be implemented as needed:

:::code-group

```php app/Database/PrefixedPascalCaseStrategy.php
use Tempest\Database\Tables\NamingStrategy;
use function Tempest\Support\str;

final class PrefixedPascalCaseStrategy implements NamingStrategy
{
    public function getName(string $model): string
    {
        return 'table_' . str($model)
            ->classBasename()
            ->pascal()
            ->toString();
    }
}
```

```php app/database.config.php
use Tempest\Database\Config\SQLiteConfig;

return new SQLiteConfig(
    path: __DIR__ . '/../database.sqlite',
    namingStrategy: new PrefixedPascalCaseStrategy(),
);
```

:::

### Data transfer object properties

Arbitrary objects can be stored in a `json` column when they are not part of the relational schema. Annotate the class with {b`#[Tempest\Mapper\SerializeAs]`} and provide a unique identifier to represent the object. The identifier must map to a single, distinct class.

:::code-group

```php app/User.php
use Tempest\Mapper\SerializeAs;

final class User implements Authenticatable
{
    public PrimaryKey $id;

    public function __construct(
        public string $email,
        #[Hashed, SensitiveParameter]
        public ?string $password,
        public Settings $settings,
    ) {}
}
```

```php app/Settings.php
#[SerializeAs('user_settings')]
final class Settings
{
    public function __construct(
        public readonly Theme $theme,
        public readonly bool $hide_sidebar_by_default,
    ) {}
}
```

```php app/Theme.php
enum Theme: string
{
    case DARK = 'dark';
    case LIGHT = 'light';
    case AUTO = 'auto';
}
```

:::

### Hashed properties

The {b`#[Tempest\Database\Hashed]`} attribute hashes the model's property during serialization. If the property is already hashed, Tempest detects this and avoids re-hashing. Common use cases include passwords, tokens, and other sensitive values.

```php app/User.php
final class User
{
    public PrimaryKey $id;

    public function __construct(
        public string $email,
        #[Hashed, SensitiveParameter]
        public ?string $password,
    ) {}
}
```

:::info
Hashing requires the `SIGNING_KEY` environment variable to be set, as it is used as the hashing key.
:::

### Encrypted properties

The {b`#[Tempest\Database\Encrypted]`} attribute encrypts the model's property during serialization and decrypts it during deserialization. If the property is already encrypted, Tempest detects this and avoids re-encrypting.

```php app/User.php
final class User
{
    // ...

    #[Encrypted]
    public ?string $accessToken;
}
```

:::info
Encryption uses the `SIGNING_KEY` environment variable as the encryption key.
:::

### Virtual properties

By default, all public properties are considered part of the model's query fields. To exclude a field from the database mapper, use the {b`#[Tempest\Database\Virtual]`} attribute.

```php
use Tempest\Database\Virtual;
use Tempest\DateTime\DateTime;
use Tempest\DateTime\Duration;

final class Book
{
    // …

    public DateTime $publishedAt;

    #[Virtual]
    public DateTime $saleExpiresAt {
        get => $this->publishedAt->add(Duration::days(5));
    }
}
```

### Hidden properties

Sensitive properties can be marked with the {b`#[Tempest\Mapper\Hidden]`} attribute to exclude them from SELECT queries. This is useful for properties like passwords, API keys, or other sensitive data that should not be fetched or exposed by default.

```php
use Tempest\Database\IsDatabaseModel;
use Tempest\Mapper\Hidden;

final class User
{
    use IsDatabaseModel;

    public string $email;

    #[Hidden]
    public string $password;

    #[Hidden]
    public ?string $apiKey = null;
}
```

Hidden properties are still included in INSERT and UPDATE queries, allowing them to be persisted to the database.

To explicitly include hidden fields in a query, use the `include()` method on the query builder:

```php
// Password is not included in the query
$user = User::select()->where('email', $email)->first();

// Password is explicitly included
$user = User::select()
    ->include('password')
    ->where('email', $email)
    ->first();

// Multiple hidden fields can be included
$user = User::select()
    ->include('password', 'apiKey')
    ->where('email', $email)
    ->first();
```

:::info
Unlike {b`#[Virtual]`} which marks computed properties that don't exist in the database, {b`#[Hidden]`} is for real database columns that should be protected from accidental exposure.
:::

:::info
The {b`#[Hidden]`} attribute also excludes properties from serialization. See the [mapper documentation](../2-features/01-mapper.md#hiding-properties-from-serialization) for more information.
:::

### The `IsDatabaseModel` trait

The {b`Tempest\Database\IsDatabaseModel`} trait provides an active record pattern. This trait enables database interaction via static methods on the model class itself.

:::code-group

```php app/Book.php
use Tempest\Database\IsDatabaseModel;
use Tempest\Validation\Rules\HasLength;
use App\Author;

final class Book
{
    use IsDatabaseModel;

    #[HasLength(min: 1, max: 120)]
    public string $title;

    public ?Author $author = null;

    /** @var \App\Chapter[] */
    public array $chapters = [];
}
```

```php "Query examples"
$book = Book::create(
    title: 'Timeline Taxi',
    author: $author,
    chapters: [
        new Chapter(index: 1, contents: '…'),
        new Chapter(index: 2, contents: '…'),
        new Chapter(index: 3, contents: '…'),
    ],
);

$books = Book::select()
    ->whereAfter('publishedAt', DateTime::now())
    ->orderBy('title', Direction::DESC)
    ->limit(10)
    ->with('author')
    ->all();

$books[0]->chapters[2]->delete();
```

:::

## Migrations

When persisting objects to the database, a table is required to store the data. A migration is a file that instructs the framework how to manage the database schema.

Tempest uses migrations to create and update databases across different environments in a consistent way.

### Writing migrations

Classes implementing the {b`Tempest\Database\MigratesUp`} or {b`Tempest\Database\MigratesDown`} interface and `.sql` files are automatically [discovered](../1-essentials/05-discovery) and registered as migrations. These files can be stored anywhere in the application.

:::code-group

```php app/CreateBooksTable.php
use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;

final class CreateBooksTable implements MigratesUp
{
    public string $name = '2024-08-12_create_books_table';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('books')
            ->primary()
            ->text('title')
            ->datetime('created_at')
            ->datetime('published_at', nullable: true)
            ->belongsTo('books.author_id', 'authors.id');
    }
}
```

```sql app/2025-01-01_create_publisher_table.sql
CREATE TABLE Publisher
(
    `id`   INTEGER,
    `name` TEXT NOT NULL
);
```

:::

:::info
The file name of `{txt}.sql` migrations and the `{txt}{:hl-type:$name:}` property of `DatabaseMigration` classes determine the order in which migrations are applied. Using the creation date as a prefix ensures chronological ordering.
:::

When using migration classes, Tempest handles the SQL dialect automatically with support for MySQL, PostgreSQL, and SQLite. When using raw SQL files, a hard-coded SQL dialect must be chosen based on database requirements.

### Up and down migrations

Up-migrations move the database schema forward. Down-migrations roll back the database schema to a previous state.

Down migrations are complex to test and manage, especially in production environments. For this reason, they require explicitly implementing the {`Tempest\Database\MigratesDown`} interface.

```php
use Tempest\Database\MigratesDown;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\DropTableStatement;

final class CreateBookTable implements MigratesDown
{
    public string $name = '2024-08-12_drop_book_table';

    public function down(): QueryStatement
    {
        return new DropTableStatement('books');
    }
}
```

### Applying migrations

Several [console commands](../3-console/02-building-console-commands) are provided to work with migrations. These commands apply, roll back, or erase and re-apply migrations.

When deploying the application to production, use `php tempest migrate:up` to apply the latest migrations.

```sh
{:hl-comment:# Apply migrations not yet run in the current environment}
./tempest migrate:up

{:hl-comment:# Drop all tables and rerun migrate:up}
./tempest migrate:fresh

{:hl-comment:# Validate the integrity of migration files}
./tempest migrate:validate
```

### Validating migrations

By default, an integrity check is performed before applying database migrations with the `migrate:up` and `migrate:fresh` commands. This validation compares the current migration hash with the one stored in the `migrations` table, if it was already applied in the environment.

If a migration file has been tampered with, the command reports it as a validation failure. This behavior can be disabled using the `--no-validate` argument.

The `migrate:validate` command can be used to validate the integrity of migrations at any point in any environment:

```sh
./tempest migrate:validate
```

:::info
Only the actual SQL query of a migration, minified and stripped of comments, is hashed during validation. Code-style changes, such as indentation, formatting, and comments do not impact the validation process.
:::

### Rehashing migrations

The `migrate:rehash` command can be used to bypass migration integrity checks and update the hashes of migrations in the database.

```sh
./tempest migrate:rehash
```

:::warning
Bypassing migration integrity checks may result in a broken database state. Use this command only when migration files are confirmed to be correct and consistent across environments.
:::

## Database seeders

Database seeders populate the database with data. These classes can fill the database with any required data. To create a seeder, implement the {b`\Tempest\Database\DatabaseSeeder`} interface.

```php
use Tempest\Database\DatabaseSeeder;
use UnitEnum;

final class BookSeeder implements DatabaseSeeder
{
    public function run(null|string|UnitEnum $database): void
    {
        query(Book::class)
            ->insert(title: 'Timeline Taxi')
            ->onDatabase($database)
            ->execute();
    }
}
```

The `$database` property is passed into the `run()` method. If a database has been specified for the seeder, this property reflects that choice.

Database seeders can be run in two ways: via the `database:seed` command or via the `migrate:fresh` command. Note that `database:seed` always _appends_ the seeded data to the existing database.

```console
./tempest database:seed
./tempest migrate:fresh --seed
```

### Multiple seeders

Multiple seeder classes can be created. Each seeder class can bring the database into a specific state or seed specific parts of the database.

When multiple seeder classes exist, Tempest prompts for selection:

```console
./tempest database:seed

 │ <em>Which seeders do you want to run?</em>
 │ / <dim>Filter...</dim>
 │ → ⋅ Tests\Tempest\Fixtures\MailingSeeder
 │   ⋅ Tests\Tempest\Fixtures\InvoiceSeeder
```

Both the `database:seed` and `migrate:fresh` commands also allow to pick one specific seeder or run all seeders automatically.

```console
./tempest database:seed --all
./tempest database:seed --seeder="Tests\Tempest\Fixtures\MailingSeeder"

./tempest migrate:fresh --seed --all
./tempest migrate:fresh --seeder="Tests\Tempest\Fixtures\MailingSeeder"
```

### Seeding on multiple databases

Seeders support multiple databases via the `--database` option. See the [Multiple databases](#multiple-databases) section for more information.

```console
./tempest database:seed --database="backup"
./tempest migrate:fresh --database="main"
```

## Multiple databases

Tempest supports connecting to multiple databases simultaneously. This is useful for transferring data between databases or building multi-tenant systems.

### Connecting to multiple databases

To connect to multiple databases, create multiple database config files and attach a tag to each database config object:

:::code-group

```php app/database.config.php
use Tempest\Database\Config\SQLiteConfig;

return new SQLiteConfig(
    path: __DIR__ . '/../database.sqlite',
    tag: 'main',
);
```

```php app/database-backup.config.php
use Tempest\Database\Config\SQLiteConfig;

return new SQLiteConfig(
    path: __DIR__ . '/../database-backup.sqlite',
    tag: 'backup',
);
```

:::

Enums provide better refactorability when used as tags:

```php app/database-backup.config.php
use Tempest\Database\Config\SQLiteConfig;
use App\Database\DatabaseType;

return new SQLiteConfig(
    path: __DIR__ . '/../database-backup.sqlite',
    tag: DatabaseType::BACKUP,
);
```

:::info
The default connection is the connection without a tag.
:::

### Querying multiple databases

With multiple databases configured, several approaches exist for using them when working with queries or models. The first approach is to inject separate database instances using their tags:

```php
use Tempest\Database\Database;
use Tempest\Container\Tag;
use App\Database\DatabaseType;
use function Tempest\Database\query;

final class DatabaseBackupCommand
{
    public function __construct(
        private Database $main,
        #[Tag(DatabaseType::BACKUP)] private Database $backup,
    ) {}

    public function __invoke(): void
    {
        $books = $this->main->fetch(
            query(Book::class)
                ->select()
                ->where('published_at < ?', '2025-01-01')
        );

        $this->backup->execute(
            query(Book::class)->insert(...$books)
        );
    }
}
```

A shorthand approach is available that does not require injecting multiple database instances:

```php
use App\Database\DatabaseType;
use function Tempest\Database\query;

final class DatabaseBackupCommand
{
    public function __invoke(): void
    {
        $books = query(Book::class)
            ->select()
            ->where('published_at < ?', '2025-01-01')
            ->onDatabase(DatabaseType::MAIN)
            ->all();

        query(Book::class)
            ->insert(...$books)
            ->onDatabase(DatabaseType::BACKUP)
            ->execute();
    }
}
```

The same approach works with active-record style models:

```php
use App\Database\DatabaseType;

final class DatabaseBackupCommand
{
    public function __invoke(): void
    {
        $books = Book::select()
            ->where('published_at < ?', '2025-01-01')
            ->onDatabase(DatabaseType::MAIN)
            ->all();

        Book::insert(...$books)
            ->onDatabase(DatabaseType::BACKUP)
            ->execute();
    }
}
```

### Migrating multiple databases

To run migrations on a specific database, you must specify the `database` flag to the migration command:

```sh
./tempest migrate:up --database=main
./tempest migrate:down --database=backup
./tempest migrate:fresh --database=main
./tempest migrate:validate --database=backup
```

:::info
When no database is specified, the default database is used. The default database is the one without a tag.
:::

### Database-specific migrations

Some migrations may need to run only on specific databases. Any database migration class can implement {b`Tempest\Database\ShouldMigrate`}, which adds a `shouldMigrate()` method to determine whether a migration should run based on the database:

```php
use Tempest\Database\Database;
use Tempest\Database\MigratesUp;
use Tempest\Database\ShouldMigrate;

final class MigrationForBackup implements MigratesUp, ShouldMigrate
{
    public string $name = '…';

    public function shouldMigrate(Database $database): bool
    {
        return $database->tag === DatabaseType::BACKUP;
    }

    public function up(): QueryStatement
    { /* … */ }
}
```

### Dynamic databases

In systems with dynamic databases, such as multi-tenant systems, a hard-coded tag may not always be available to configure and resolve the correct database. In these cases, dynamic databases can be added as needed:

```php
final class ConnectTenant
{
    public function __invoke(string $tenantId): void
    {
        $this->container->config(new SQLiteConfig(
            path: __DIR__ . "/tenant-{$tenantId}.sqlite",
            tag: $tenantId,
        ));
    }
}
```

Migrations can be run programmatically on dynamically defined databases using the {b`Tempest\Database\Migrations\MigrationManager`}:

```php
use Tempest\Database\Migrations\MigrationManager;

final class OnboardTenant
{
    public function __construct(
        private MigrationManager $migrationManager,
    ) {}

    public function __invoke(string $tenantId): void
    {
        $setupMigrations = [
            new CreateMigrationsTable(),
            // Additional migrations
        ];

        foreach ($setupMigrations as $migration) {
            $this->migrationManager->onDatabase($tenantId)->executeUp($migration);
        }
    }
}
```

Dynamic database connections should be registered within the application's entry points. This can be accomplished with [middleware](/main/essentials/routing#route-middleware) or with a [kernel event hook](/main/extra-topics/package-development#provider-classes):

```php
use Tempest\Container\Container;
use Tempest\Router\HttpMiddleware;
use Tempest\Core\Priority;

#[Priority(Priority::HIGHEST)]
final class ConnectTenantMiddleware implements HttpMiddleware
{
    public function __construct(
        private Container $container,
    ) {}

    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        $tenantId = // Tenant ID resolution from request

        (new ConnectTenant)($tenantId);

        return $next($request);
    }
}
```
