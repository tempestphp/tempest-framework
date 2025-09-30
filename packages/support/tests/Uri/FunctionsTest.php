<?php

declare(strict_types=1);

namespace Tempest\Support\Tests\Uri;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

use function Tempest\Support\Uri\get_fragment;
use function Tempest\Support\Uri\get_host;
use function Tempest\Support\Uri\get_password;
use function Tempest\Support\Uri\get_path;
use function Tempest\Support\Uri\get_port;
use function Tempest\Support\Uri\get_query;
use function Tempest\Support\Uri\get_scheme;
use function Tempest\Support\Uri\get_segments;
use function Tempest\Support\Uri\get_user;
use function Tempest\Support\Uri\merge_query;
use function Tempest\Support\Uri\set_fragment;
use function Tempest\Support\Uri\set_host;
use function Tempest\Support\Uri\set_password;
use function Tempest\Support\Uri\set_path;
use function Tempest\Support\Uri\set_port;
use function Tempest\Support\Uri\set_query;
use function Tempest\Support\Uri\set_scheme;
use function Tempest\Support\Uri\set_user;
use function Tempest\Support\Uri\without_query;

final class FunctionsTest extends TestCase
{
    #[Test]
    #[TestWith(['https://example.com', ['foo' => 'bar'], 'https://example.com?foo=bar'])]
    #[TestWith(['https://example.com?existing=value', ['foo' => 'bar'], 'https://example.com?foo=bar'])]
    #[TestWith(['https://example.com', ['foo' => 'bar', 'baz' => 'qux'], 'https://example.com?foo=bar&baz=qux'])]
    #[TestWith(['https://example.com/path', [], 'https://example.com/path'])]
    #[TestWith(['malformed-uri', ['foo' => 'bar'], 'malformed-uri?foo=bar'])]
    #[TestWith(['https://example.com', ['foo'], 'https://example.com?foo'])]
    #[TestWith(['https://example.com', ['foo' => true], 'https://example.com?foo=true'])]
    #[TestWith(['https://example.com', ['foo' => false], 'https://example.com?foo=false'])]
    #[TestWith(['https://example.com', ['foo' => ['bar' => 'baz']], 'https://example.com?foo%5Bbar%5D=baz'])]
    public function set_query(string $uri, array $query, string $expected): void
    {
        $this->assertSame($expected, set_query($uri, ...$query));
    }

    #[Test]
    #[TestWith(['https://example.com?foo=bar', ['foo' => 'bar']])]
    #[TestWith(['https://example.com?foo=bar&baz=qux', ['foo' => 'bar', 'baz' => 'qux']])]
    #[TestWith(['https://example.com', []])]
    #[TestWith(['https://example.com?', []])]
    #[TestWith(['malformed-uri', []])]
    public function get_query(string $uri, array $expected): void
    {
        $this->assertSame($expected, get_query($uri));
    }

    #[Test]
    #[TestWith(['https://example.com', 'frieren', 'https://example.com#frieren'])]
    #[TestWith(['https://example.com#old', 'stark', 'https://example.com#stark'])]
    #[TestWith(['https://example.com/path?query=value', 'fern', 'https://example.com/path?query=value#fern'])]
    #[TestWith(['malformed-uri', 'fragment', 'malformed-uri#fragment'])]
    public function set_fragment(string $uri, string $fragment, string $expected): void
    {
        $this->assertSame($expected, set_fragment($uri, $fragment));
    }

    #[Test]
    #[TestWith(['https://example.com#frieren', 'frieren'])]
    #[TestWith(['https://example.com', null])]
    #[TestWith(['https://example.com#', ''])]
    #[TestWith(['malformed-uri', null])]
    public function get_fragment(string $uri, ?string $expected): void
    {
        $this->assertSame($expected, get_fragment($uri));
    }

    #[Test]
    #[TestWith(['https://example.com', 'flamme.org', 'https://flamme.org'])]
    #[TestWith(['https://old.com/path', 'neue.com', 'https://neue.com/path'])]
    #[TestWith(['http://user:pass@old.com:8080', 'serie.com', 'http://user:pass@serie.com:8080'])]
    #[TestWith(['malformed-uri', 'domain.com', '//domain.com/malformed-uri'])]
    public function set_host(string $uri, string $host, string $expected): void
    {
        $this->assertSame($expected, set_host($uri, $host));
    }

    #[Test]
    #[TestWith(['https://example.com', 'example.com'])]
    #[TestWith(['https://flamme.org/path', 'flamme.org'])]
    #[TestWith(['http://user:pass@serie.com:8080', 'serie.com'])]
    #[TestWith(['relative/path', null])]
    #[TestWith(['malformed-uri', null])]
    public function get_host(string $uri, ?string $expected): void
    {
        $this->assertSame($expected, get_host($uri));
    }

    #[Test]
    #[TestWith(['https://example.com', 'http', 'http://example.com'])]
    #[TestWith(['http://example.com', 'https', 'https://example.com'])]
    #[TestWith(['ftp://files.example.com', 'sftp', 'sftp://files.example.com'])]
    #[TestWith(['malformed-uri', 'https', 'https:malformed-uri'])]
    public function set_scheme(string $uri, string $scheme, string $expected): void
    {
        $this->assertSame($expected, set_scheme($uri, $scheme));
    }

    #[Test]
    #[TestWith(['https://example.com', 'https'])]
    #[TestWith(['http://example.com', 'http'])]
    #[TestWith(['ftp://files.example.com', 'ftp'])]
    #[TestWith(['//example.com', null])]
    #[TestWith(['malformed-uri', null])]
    public function get_scheme(string $uri, ?string $expected): void
    {
        $this->assertSame($expected, get_scheme($uri));
    }

    #[Test]
    #[TestWith(['https://example.com', 8080, 'https://example.com:8080'])]
    #[TestWith(['https://example.com:80', 9000, 'https://example.com:9000'])]
    #[TestWith(['http://user:pass@example.com', 3000, 'http://user:pass@example.com:3000'])]
    #[TestWith(['malformed-uri', 8080, 'malformed-uri'])]
    public function set_port(string $uri, int $port, string $expected): void
    {
        $this->assertSame($expected, set_port($uri, $port));
    }

    #[Test]
    #[TestWith(['https://example.com:8080', 8080])]
    #[TestWith(['https://example.com', null])]
    #[TestWith(['http://user:pass@example.com:3000', 3000])]
    #[TestWith(['malformed-uri', null])]
    public function get_port(string $uri, ?int $expected): void
    {
        $this->assertSame($expected, get_port($uri));
    }

    #[Test]
    #[TestWith(['https://example.com', 'frieren', 'https://frieren@example.com'])]
    #[TestWith(['https://old@example.com', 'fern', 'https://fern@example.com'])]
    #[TestWith(['https://old:pass@example.com', 'stark', 'https://stark:pass@example.com'])]
    #[TestWith(['malformed-uri', 'user', '//user@malformed-uri'])]
    public function set_user(string $uri, string $user, string $expected): void
    {
        $this->assertSame($expected, set_user($uri, $user));
    }

    #[Test]
    #[TestWith(['https://frieren@example.com', 'frieren'])]
    #[TestWith(['https://example.com', null])]
    #[TestWith(['https://fern:secret@example.com', 'fern'])]
    #[TestWith(['malformed-uri', null])]
    public function get_user(string $uri, ?string $expected): void
    {
        $this->assertSame($expected, get_user($uri));
    }

    #[Test]
    #[TestWith(['https://user@example.com', 'magic', 'https://user:magic@example.com'])]
    #[TestWith(['https://user:old@example.com', 'spell', 'https://user:spell@example.com'])]
    #[TestWith(['https://example.com', 'secret', 'https://:secret@example.com'])]
    #[TestWith(['malformed-uri', 'pass', '//:pass@malformed-uri'])]
    public function set_password(string $uri, string $pass, string $expected): void
    {
        $this->assertSame($expected, set_password($uri, $pass));
    }

    #[Test]
    #[TestWith(['https://user:magic@example.com', 'magic'])]
    #[TestWith(['https://example.com', null])]
    #[TestWith(['https://user@example.com', null])]
    #[TestWith(['malformed-uri', null])]
    public function get_password(string $uri, ?string $expected): void
    {
        $this->assertSame($expected, get_password($uri));
    }

    #[Test]
    #[TestWith(['https://example.com', '/frieren/journey', 'https://example.com/frieren/journey'])]
    #[TestWith(['https://example.com/old/path', '/new/path', 'https://example.com/new/path'])]
    #[TestWith(['https://example.com?query=value', '/path', 'https://example.com/path?query=value'])]
    #[TestWith(['malformed-uri', '/path', '/path'])]
    public function set_path(string $uri, string $path, string $expected): void
    {
        $this->assertSame($expected, set_path($uri, $path));
    }

    #[Test]
    #[TestWith(['https://example.com/frieren/journey', '/frieren/journey'])]
    #[TestWith(['https://example.com', null])]
    #[TestWith(['https://example.com/', '/'])]
    #[TestWith(['malformed-uri', 'malformed-uri'])]
    public function get_path(string $uri, ?string $expected): void
    {
        $this->assertSame($expected, get_path($uri));
    }

    #[Test]
    #[TestWith(['https://example.com/path/to/resource', ['path', 'to', 'resource']])]
    #[TestWith(['https://example.com/', []])]
    #[TestWith(['https://example.com', []])]
    #[TestWith(['https://example.com/single', ['single']])]
    #[TestWith(['https://example.com/path/with/empty//segments', ['path', 'with', 'empty', 'segments']])]
    #[TestWith(['malformed-uri/path/segments', ['malformed-uri', 'path', 'segments']])]
    public function get_segments(string $uri, array $expected): void
    {
        $this->assertSame($expected, get_segments($uri));
    }

    #[Test]
    #[TestWith(['https://example.com?foo=bar', ['baz' => 'qux'], 'https://example.com?foo=bar&baz=qux'])]
    #[TestWith(['https://example.com', ['foo' => 'bar'], 'https://example.com?foo=bar'])]
    #[TestWith(['https://example.com?existing=value', ['new' => 'param'], 'https://example.com?existing=value&new=param'])]
    #[TestWith(['https://example.com?foo=old', ['foo' => 'new'], 'https://example.com?foo=new'])]
    #[TestWith(['malformed-uri?foo=bar', ['baz' => 'qux'], 'malformed-uri?foo=bar&baz=qux'])]
    public function merge_query(string $uri, array $query, string $expected): void
    {
        $this->assertSame($expected, merge_query($uri, ...$query));
    }

    #[Test]
    #[TestWith(['https://example.com?foo=bar&baz=qux', ['foo'], 'https://example.com?baz=qux'])]
    #[TestWith(['https://example.com?foo=bar&baz=qux', ['foo', 'baz'], 'https://example.com'])]
    #[TestWith(['https://example.com?foo=bar&baz=qux', ['foo' => 'bar'], 'https://example.com?baz=qux'])]
    #[TestWith(['https://example.com?foo=bar&baz=qux', ['foo' => 'wrong'], 'https://example.com?foo=bar&baz=qux'])]
    #[TestWith(['https://example.com', ['nonexistent'], 'https://example.com'])]
    #[TestWith(['malformed-uri?foo=bar', ['foo'], 'malformed-uri'])]
    public function without_query(string $uri, array $query, string $expected): void
    {
        $this->assertSame($expected, without_query($uri, ...$query));
    }

    #[Test]
    public function complex_uri_manipulation(): void
    {
        $uri = 'https://user:pass@example.com:8080/old/path?old=value#old-fragment';

        $uri = set_scheme($uri, 'http');
        $uri = set_host($uri, 'neue.com');
        $uri = set_port($uri, 9000);
        $uri = set_user($uri, 'frieren');
        $uri = set_password($uri, 'magic');
        $uri = set_path($uri, '/journey/north');
        $uri = set_query($uri, destination: 'aureole', companion: 'fern');
        $uri = set_fragment($uri, 'adventure');

        $expected = 'http://frieren:magic@neue.com:9000/journey/north?destination=aureole&companion=fern#adventure';
        $this->assertSame($expected, $uri);

        $this->assertSame('http', get_scheme($uri));
        $this->assertSame('neue.com', get_host($uri));
        $this->assertSame(9000, get_port($uri));
        $this->assertSame('frieren', get_user($uri));
        $this->assertSame('magic', get_password($uri));
        $this->assertSame('/journey/north', get_path($uri));
        $this->assertSame(['destination' => 'aureole', 'companion' => 'fern'], get_query($uri));
        $this->assertSame('adventure', get_fragment($uri));
    }

    #[Test]
    public function segments_and_query_manipulation(): void
    {
        $uri = 'https://example.com/frieren/journey/north?mage=true&party=4';

        $this->assertSame(['frieren', 'journey', 'north'], get_segments($uri));

        $uri = merge_query($uri, destination: 'aureole');
        $this->assertSame(['mage' => 'true', 'party' => '4', 'destination' => 'aureole'], get_query($uri));

        $uri = without_query($uri, 'party');
        $this->assertSame(['mage' => 'true', 'destination' => 'aureole'], get_query($uri));

        $uri = without_query($uri, mage: 'true');
        $this->assertSame(['destination' => 'aureole'], get_query($uri));
    }
}
