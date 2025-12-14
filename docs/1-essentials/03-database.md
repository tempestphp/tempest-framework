---
title: Database
description: "Tempest's database component allows you to persist data to SQLite, MySQL and PostgreSQL databases. You can use our powerful query builder, or build truly decoupled models to interact with your database of choice."
keywords: ["experimental", "orm", "database", "sqlite", "postgresql", "pgsql", "mysql", "query", "sql", "connection", "models"]
---

:::warning
Tempest's database component is currently experimental and is not covered by our backwards compatibility promise.
:::

## Connecting to a database

By default, Tempest will connect to a local SQLite database located in its internal storage, `.tempest/database.sqlite`. You may override the default database connection by creating a [configuration file](../1-essentials/06-configuration.md#configuration-files):

```php app/Config/database.config.php
use Tempest\Database\Config\SQLiteConfig;
use function Tempest\root_path;

return new SQLiteConfig(
    path: root_path('database.sqlite'),
);
```

Alternatively, you can connect to another database by returning another configuration object from file. You may choose between {b`Tempest\Database\Config\SQLiteConfig`}, {b`Tempest\Database\Config\MysqlConfig`}, or {b`Tempest\Database\Config\PostgresConfig`}:

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

There are multiple ways to query the database, but all of them eventually do the same thing: execute a {b`Tempest\Database\Query`} on the {b`Tempest\Database\Database`} class. The most straight-forward way to query the database is thus by injecting {b`Tempest\Database\Database`}:

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

Manually building and executing queries gives you the most flexibility. However, using Tempest's query builder is more convenient—it gives you fluent methods to build queries without needing to worry about database-specific syntax differences.

```php
use function Tempest\Database\query;

final class BookRepository
{
    public function findById(int $id): array
    {
        return query('books')
            ->select('id', 'title')
            ->where('id = ?', $id)
            ->first();
    }
}
```

If preferred, you can combine both methods and use the query builder to build a query that's executed on a database:

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

### Query builders

There are multiple types of query builders, all of them are available via the `query()` function. If you prefer to manually create a query builder though, you can also instantiate them directly:

```php
use Tempest\Database\Builder\QueryBuilders\SelectQueryBuilder;

$builder = new SelectQueryBuilder('books');
```

Currently, there are five query builders shipped with Tempest:

- {`Tempest\Database\Builder\QueryBuilders\SelectQueryBuilder`}
- {`Tempest\Database\Builder\QueryBuilders\InsertQueryBuilder`}
- {`Tempest\Database\Builder\QueryBuilders\UpdateQueryBuilder`}
- {`Tempest\Database\Builder\QueryBuilders\DeleteQueryBuilder`}
- {`Tempest\Database\Builder\QueryBuilders\CountQueryBuilder`}

Each of them has their own unique methods that work within their scope. You can discover them via your IDE, or check them out on [GitHub](https://github.com/tempestphp/tempest-framework/tree/main/packages/database/src/Builder/QueryBuilders).

Finally, you can make your own query builders if you want by implementing the {b`Tempest\Database\Builder\QueryBuilders\BuildsQuery`} interface.

## Models

A common use case in many applications is to represent persisted data as objects within your codebase. This is where model classes come in. Tempest tries to decouple models as best as possible from the database, so any object with public typed properties can represent a table.

These objects don't have to implement any interface—they may be plain-old PHP objects:

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

Because model objects aren't tied to the database specifically, Tempest's [mapper](../2-features/01-mapper.md) can map data from many different sources to them. For instance, you can persist your models as JSON instead of a database, if you want to:

```php
use function Tempest\Mapper\map;

$books = map($json)->collection()->to(Book::class); // from JSON source to Book collection
$json = map($books)->toJson(); // from Book collection to JSON
```

That being said, persistence most often happens on the database level, so let's take a look at how to deal with models that persist to the database.

### Models and query builders

The easiest way of persisting models to a database is by using the query builder. Tempest's query builder cannot just deal with tables and arrays, but also knows how to map data from and to model objects. All you need to do is specify which class you want to query, and Tempest will do the rest.

In the following example, we'll query the table related to the `Book` model, we'll select all fields, load its related `chapters` and `author` as well, specify the ID of the book we're searching, and then return the first result:

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
            ->where('id = ?', $id)
            ->first();
    }
}
```

Tempest will infer all relation-type information from the model class, specifically by looking at the property types. For example, a property with the `Author` type is assumed to be a "belongs to" relation, while a property with the `/** @var \App\Chapter[] */` docblock is assumed to be a "has many" relation on the `Chapter` model.

Apart from selecting models, it's of course possible to use any other query builder with them as well:

```php
use App\Models\Book;
use function Tempest\Database\query;

final class BookRepository
{
    public function create(Book $book): Id
    {
        return query(Book::class)
            ->insert($book)
            ->execute();
    }
}
```

:::info
Currently it's not possible to insert or update {b`Tempest\Database\HasMany`} or {b`Tempest\Database\HasOne`} relations directly by inserting or updating the parent model. You should first insert or update the parent model and then insert or update the child models separately. This shortcoming will be fixed in [the future](https://github.com/tempestphp/tempest-framework/issues/1087).
:::

### Model relations

As mentioned before, Tempest will infer relations based on type information it gets from the model class. A public property with a reference to another class will be assumed to be a {b`Tempest\Database\BelongsTo`} relation, while a property with a docblock that defines an array type is assumed to be a {b`Tempest\Database\HasMany`} relation.

```php
use App\Author;

final class Book
{
    // This is a BelongsTo relation:
    public ?Author $author = null;

    // This is a HasMany relation:
    /** @var \App\Chapter[] */
    public array $chapters = [];
}
```

:::warning
Relation types in docblocks must always be fully qualified, and not use short class names.
:::

Tempest will infer all the information it needs to build the right queries for you. However, there might be cases where property names and type information don't map one-to-one on your database schema. In that case you can use dedicated attributes to define relations.

### Relation attributes

Tempest will infer relation names based on property names and types. However, you can override these names with the {b`#[Tempest\Database\HasMany]`}, {b`#[Tempest\Database\HasOne]`}, and {b`#[Tempest\Database\BelongsTo]`} attributes. These attributes all take two optional arguments:

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
    #[HasMany(relationJoin: 'chapters.uuid', ownerJoin: 'books.chapter_uuid')]
    public array $chapters = [];

    #[HasOne(relationJoin: 'books.uuid', ownerJoin: 'isbns.book_uuid')]
    public Isbn $isbn = [];
}
```

The **owner** part of the relation resembles the table that _owns_ the relation. In other words: the table which has a column referencing another table. The **relation** part resembles the table that's _being referenced by another table_. This is why the {b`Tempest\Database\BelongsTo`} relation starts with _the owner join_, while both {b`Tempest\Database\HasMany`} and {b`Tempest\Database\HasOne`} start with _the relation join_.

Finally, it's important to mention that you don't have to write the full owner or relation join including both the table and the field. You can also use the field name without the table name, in which case the table name is inferred from the related model:

```php
use Tempest\Database\BelongsTo;
use Tempest\Database\HasMany;
use Tempest\Database\HasOne;

final class Book
{
    #[BelongsTo(ownerJoin: 'author_uuid', relationJoin: 'uuid')]
    public ?Author $author = null;

    /** @var \App\Chapter[] */
    #[HasMany(relationJoin: 'uuid', ownerJoin: 'chapter_uuid')]
    public array $chapters = [];

    #[HasOne(relationJoin: 'uuid', ownerJoin: 'book_uuid')]
    public Isbn $isbn = [];
}
```

### Hashed properties

The {`#[Tempest\Database\Hashed]`} attribute will hash the model's property during serialization. If the property was already hashed, Tempest will detect that and avoid re-hashing it.

```php
final class User
{
    public PrimaryKey $id;

    public string $email;

    #[Hashed]
    public ?string $password;
}
```

Hashing requires the `SIGNING_KEY` environment variable to be set, as it's used as the hashing key.

### Encrypted properties

The {`#[Tempest\Database\Encrypted]`} attribute will encrypt the model's property during serialization and decrypt it during deserialization. If the property was already encrypted, Tempest will detect that and avoid re-encrypting it.

```php
final class User
{
    // ...

    #[Encrypted]
    public ?string $accessToken;
}
```

The encryption key is taken from the `SIGNING_KEY` environment variable.

### Data transfer object properties

You can store arbitrary objects directly in a `json` column when they don’t need to be part of the relational schema.

To do this, annotate the class with `⁠#[Tempest\Mapper\SerializeAs]` and provide a unique identifier for the object’s serialized form. The identifier must map to a single, distinct class.

```php
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

#[SerializeAs('user_settings')]
final class Settings
{
    public function __construct(
        public readonly Theme $theme,
        public readonly bool $hide_sidebar_by_default,
    ) {}
}

enum Theme: string
{
    case DARK = 'dark';
    case LIGHT = 'light';
    case AUTO = 'auto';
}
```

### Table names

Tempest will infer the table name for a model class based on the model's classname. By default the table name will by the pluralized, `snake_cased` version of that classname. You can override this name by using the {b`Tempest\Database\Table`} attribute:

```php
use Tempest\Database\Table;

#[Table('table_books')]
final class Book
{
    // …
}
```

You can also configure a completely new naming strategy for all your models at once by creating a {b`Tempest\Database\Tables\NamingStrategy`} and attaching it to your database config:

```php
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

```php app/Config/database.config.php
use Tempest\Database\Config\SQLiteConfig;

return new SQLiteConfig(
    path: __DIR__ . '/../database.sqlite',
    namingStrategy: new PrefixedPascalCaseStrategy(),
);
```

### Virtual properties

By default, all public properties are considered to be part of the model's query fields. To exclude a field from the database mapper, you may use the {b`Tempest\Database\Virtual`} attribute.

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
        get => $this->publishedAt->add(Duration::days(5)));
    }
}
```

### The `IsDatabaseModel` trait

People who are used to Eloquent might prefer a more "active record" style to handling their models. In that case, there's the {b`Tempest\Database\IsDatabaseModel`} trait which you can use in your model classes:

```php
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

Thanks to the {b`Tempest\Database\IsDatabaseModel`} trait, you can interact with the database directly via the model class:

```php
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
    ->where('publishedAt > ?', new DateTimeImmutable())
    ->orderBy('title DESC')
    ->limit(10)
    ->with('author')
    ->all();

$books[0]->chapters[2]->delete();
```

### Using UUIDs as primary keys

By default, Tempest uses auto-incrementing integers as primary keys. However, you can use UUIDs as primary keys instead by marking a {b`Tempest\Database\PrimaryKey`} property with the {b`#[Tempest\Database\Uuid]`} attribute. Tempest will automatically generate a UUID v7 value whenever a new model is created:

```php src/Books/Book.php
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

Within migrations, you may specify `uuid: true` to the `primary()` method, or directly use `uuid()`:

```php src/Books/CreateBooksTable.php
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
Currently, the `IsDatabaseModel` trait already provides a primary `$id` property. It is therefore not possible to use UUIDs alongside `IsDatabaseModel`.
:::

## Migrations

When you're persisting objects to the database, you'll need table to store its data in. A migration is a file instructing the framework how to manage that database schema. Tempest uses migrations to create and update databases across different environments.

### Writing migrations

Thanks to [discovery](../4-internals/02-discovery), `.sql` files and classes implementing the {b`Tempest\Database\DatabaseMigration`} interface are automatically registered as migrations, which means they can be stored anywhere.

```php
use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;

final class CreateBookTable implements MigratesUp
{
    public string $name = '2024-08-12_create_book_table';

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

:::info
The file name of `{txt}.sql` migrations and the `{txt}{:hl-type:$name:}` property of `DatabaseMigration` classes are used to determine the order in which they are applied. A good practice is to use their creation date as a prefix.
:::

Note that when using migration classes combined with query statements, Tempest will take care of the SQL dialect for you, there's support for MySQL, Postgresql, and SQLite. When using raw sql files, you'll have to pick a hard-coded SQL dialect, depending on your database requirements.

### Up- and down migrations

Tempest's recommendation is to only use up-migrations, which move the database's schema forward. There is also the option to create down-migrations, migrations that can roll back the schema of the database to a previous state. Dealing with down migrations is tricky, though, especially in production environments. That's why you need to explicitly implement another interface to do so: {`Tempest\Database\MigratesDown`}.

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

A few [console commands](../3-console/02-building-console-commands) are provided to work with migrations. They are used to apply, rollback, or erase and re-apply them. When deploying your application to production, you should use the `php tempest migrate:up` to apply the latest migrations.

```sh
{:hl-comment:# Applies migrations that have not been run in the current environment:}
./tempest migrate:up

{:hl-comment:# Execute the down migrations:}
./tempest migrate:down

{:hl-comment:# Drops all tables and rerun migrate:up:}
./tempest migrate:fresh

{:hl-comment:# Validates the integrity of migration files:}
./tempest migrate:validate
```

### Validating migrations

By default, an integrity check is done before applying database migrations with the `migrate:up` and `migrate:fresh` commands. This validation works by comparing the current migration hash with the one stored in the `migrations` table, if it was already applied in your environment.

If a migration file has been tampered with, the command will report it as a validation failure. Note that you may opt-out of this behavior by using the `--no-validate` argument.

Additionally, you may use the `migrate:validate` command to validate the integrity of migrations at any point, in any environment:

```sh
./tempest migrate:validate
```

:::tip
Only the actual SQL query of a migration, minified and stripped of comments, is hashed during validation. This means that code-style changes, such as indentation, formatting, and comments will not impact the validation process.
:::

### Rehashing migrations

You may use the `migrate:rehash` command to bypass migration integrity checks and update the hashes of migrations in the database.

```sh
./tempest migrate:rehash
```

:::warning
Note that deliberately bypassing migration integrity checks may result in a broken database state. Only use this command when necessary if you are confident that your migration files are correct and consistent across environments.
:::

## Database seeders

Whenever you need to fill your database with dummy data, you can provide database seeders. These are classes that are used to fill your database with whatever data you want. To get started, you should implement the {`\Tempest\Database\DatabaseSeeder`} interface.

```php
use Tempest\Database\DatabaseSeeder;
use UnitEnum;

final class BookSeeder implements DatabaseSeeder
{
    public function run(null|string|UnitEnum $database): void
    {
        query(Book::class)
            ->insert(
                title: 'Timeline Taxi',
            )
            ->onDatabase($database)
            ->execute();
    }
}
```

Note how the `$database` property is passed into the `run()` method. In case a user has specified a database for this seeder to run on, this property will reflect that choice.

Running database seeders can be done in two ways: either via the `database:seed` command, or via the `migrate:fresh` command. Not that `database:seed` will always append the seeded data on the existing database.

```console
./tempest database:seed
./tempest migrate:fresh --seed
```

### Multiple seeders

If you want to, you can create multiple seeder classes. Each seeder class could be used to bring the database into a specific state, or you could use multiple seeder classes to seed specific parts of your database.

Whenever you have multiple seeder classes, Tempest will prompt you which ones to run:

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

Seeders have built-in support for multiple databases, which you can specify with the `--database` option. Continue reading to learn more about multiple databases.

```console
./tempest database:seed --database="backup"
./tempest migrate:fresh --database="main"
```

## Multiple databases

Tempest supports connecting to multiple databases at once. This can, for example, be useful to transfer data between databases or build multi-tenant systems.

:::warning
Multiple database support on Windows is currently untested. We welcome anyone who wants to [contribute](https://github.com/tempestphp/tempest-framework/issues/1271).
:::

### Connecting to multiple databases

If you want to connect to multiple databases, you should make multiple database config files and attach a tag to each database config object:

```php app/Config/database.config.php
use Tempest\Database\Config\SQLiteConfig;

return new SQLiteConfig(
    path: __DIR__ . '/../database.sqlite',
    tag: 'main',
);
```

```php app/Config/database-backup.config.php
use Tempest\Database\Config\SQLiteConfig;

return new SQLiteConfig(
    path: __DIR__ . '/../database-backup.sqlite',
    tag: 'backup',
);
```

When preferred, you can use a self-defined enum as the tag as well:

```php app/Config/database-backup.config.php
use Tempest\Database\Config\SQLiteConfig;
use App\Database\DatabaseType;

return new SQLiteConfig(
    path: __DIR__ . '/../database-backup.sqlite',
    tag: DatabaseType::BACKUP,
);
```

:::info
Note that the _default_ connection will always be the connection without a tag.
:::

### Querying multiple databases

With multiple databases configured, how do you actually use them when working with queries or models? There are several ways of doing so. The first approach is to manually inject separate database instances by using their tag:

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

It might be quite cumbersome to write so much code everywhere if you're working with multiple databases though. That's why there's a shorthand available that doesn't require you to inject multiple database instances:

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

Note that the same is possible when using active-record style models:

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
When no database is provided, the default database will be used, this is the database that doesn't have a specific tag attached to it.
:::

### Database-specific migrations

Sometimes you might only want to run specific migrations on specific databases. Any database migration class may implement the {b`Tempest\Database\ShouldMigrate`}, which adds a `shouldMigrate()` method to determine whether a migration should run or not, based on the database:

```php
use Tempest\Database\Database;
use Tempest\Database\DatabaseMigration;
use Tempest\Database\ShouldMigrate;

final class MigrationForBackup implements DatabaseMigration, ShouldMigrate
{
    public string $name = '…';

    public function shouldMigrate(Database $database): bool
    {
        return $database->tag === 'backup';
    }

    public function up(): QueryStatement
    { /* … */ }

    public function down(): QueryStatement
    { /* … */ }
}
```

### Dynamic databases

In systems with dynamic databases, like, for example, multi-tenant systems; you might not always have a hard-coded tag available to configure and resolve the right database. In those cases, it's trivial to add as many dynamic databases as you'd like:

```php
final class ConnectTenant
{
    public function __invoke(string $tenantId): void
    {
        // Use any database config you'd like:
        $this->container->config(new SQLiteConfig(
            path: __DIR__ . "/tenant-{$tenantId}.sqlite",
            tag: $tenantId,
        ));
    }
}
```

Furthermore, you can run migrations programmatically on such dynamically defined databases using the {`Tempest\Database\Migrations\MigrationManager`}:

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
            // …
        ];

        foreach ($setupMigrations as $migration) {
            $this->migrationManager->onDatabase($tenantId)->executeUp($migration);
        }
    }
}
```

Finally, you should register your dynamic database connections as well within the entry points of your application. This could be done with [middleware](/main/essentials/routing#route-middleware), or with a [kernel event hook](/main/extra-topics/package-development#provider-classes); that's up to you:

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
        $tenantId = // Resolve tenant ID from the request

        (new ConnectTennant)($tenantId);

        return $next($request);
    }
}
```
