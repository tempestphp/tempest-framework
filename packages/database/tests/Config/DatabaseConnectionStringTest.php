<?php

declare(strict_types=1);

namespace Tempest\Database\Tests\Config;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Database\Config\DatabaseConnectionString;
use Tempest\Database\Exceptions\DatabaseConnectionStringWasInvalid;

/**
 * @internal
 */
final class DatabaseConnectionStringTest extends TestCase
{
    #[Test]
    public function components_are_parsed(): void
    {
        $connectionString = DatabaseConnectionString::from('postgres://us%3Aer:p%40ss@db.example.com:5433/tempest?sslmode=require&connect_timeout=10');

        $this->assertSame('postgres', $connectionString->scheme);
        $this->assertSame('db.example.com', $connectionString->host);
        $this->assertSame(5433, $connectionString->port);
        $this->assertSame('us:er', $connectionString->username);
        $this->assertSame('p@ss', $connectionString->password);
        $this->assertSame('/tempest', $connectionString->path);
        $this->assertSame('tempest', $connectionString->database);
        $this->assertSame(['sslmode' => 'require', 'connect_timeout' => '10'], $connectionString->parameters);
    }

    #[Test]
    public function missing_components_are_null(): void
    {
        $connectionString = DatabaseConnectionString::from('MySQL://db.example.com');

        $this->assertSame('mysql', $connectionString->scheme);
        $this->assertSame('db.example.com', $connectionString->host);
        $this->assertNull($connectionString->port);
        $this->assertNull($connectionString->username);
        $this->assertNull($connectionString->password);
        $this->assertNull($connectionString->path);
        $this->assertNull($connectionString->database);
        $this->assertSame([], $connectionString->parameters);
    }

    #[Test]
    public function unix_socket_host_is_decoded(): void
    {
        $connectionString = DatabaseConnectionString::from('postgresql://user@%2Fvar%2Frun%2Fpostgresql/tempest');

        $this->assertSame('/var/run/postgresql', $connectionString->host);
        $this->assertNull($connectionString->port);
        $this->assertSame('tempest', $connectionString->database);
    }

    #[Test]
    public function host_may_be_omitted(): void
    {
        $connectionString = DatabaseConnectionString::from('postgresql:///tempest?host=/var/run/postgresql');

        $this->assertNull($connectionString->host);
        $this->assertSame('tempest', $connectionString->database);
        $this->assertSame(['host' => '/var/run/postgresql'], $connectionString->parameters);
    }

    #[Test]
    public function ipv6_host_is_parsed(): void
    {
        $connectionString = DatabaseConnectionString::from('mysql://[::1]:3307/tempest');

        $this->assertSame('[::1]', $connectionString->host);
        $this->assertSame(3307, $connectionString->port);
    }

    #[Test]
    public function connection_string_without_authority_is_parsed(): void
    {
        $connectionString = DatabaseConnectionString::from('sqlite::memory:');

        $this->assertSame('sqlite', $connectionString->scheme);
        $this->assertNull($connectionString->host);
        $this->assertSame(':memory:', $connectionString->path);
    }

    #[Test]
    public function connection_string_without_scheme_throws(): void
    {
        $this->expectException(DatabaseConnectionStringWasInvalid::class);

        DatabaseConnectionString::from('db.example.com');
    }

    #[Test]
    public function invalid_port_throws(): void
    {
        $this->expectException(DatabaseConnectionStringWasInvalid::class);

        DatabaseConnectionString::from('mysql://db.example.com:port/tempest');
    }
}
