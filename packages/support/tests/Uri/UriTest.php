<?php

declare(strict_types=1);

namespace Tempest\Support\Tests\Uri;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Support\Uri\Uri;

final class UriTest extends TestCase
{
    #[Test]
    public function is_stringable(): void
    {
        $uri = Uri::from('https://example.com');

        $this->assertSame('https://example.com', $uri->toString());
        $this->assertSame('https://example.com', (string) $uri);
    }

    #[Test]
    public function can_be_made_statically(): void
    {
        $this->assertSame('https://example.com', Uri::from('https://example.com')->toString());
    }

    #[Test]
    public function scheme_manipulation(): void
    {
        $uri = Uri::from('https://example.com');

        $this->assertSame('https', $uri->scheme);

        $clone = $uri->withScheme('http');
        $this->assertSame('http://example.com', $clone->toString());
        $this->assertSame('http', $clone->scheme);

        $this->assertSame('https://example.com', $uri->toString());
    }

    #[Test]
    public function user_manipulation(): void
    {
        $uri = Uri::from('https://example.com');

        $this->assertNull($uri->user);

        $clone = $uri->withUser('frieren');
        $this->assertSame('https://frieren@example.com', $clone->toString());
        $this->assertSame('frieren', $clone->user);

        $this->assertNull($uri->user);
    }

    #[Test]
    public function password_manipulation(): void
    {
        $uri = Uri::from('https://frieren@example.com');

        $this->assertNull($uri->password);

        $clone = $uri->withPassword('magic');
        $this->assertSame('https://frieren:magic@example.com', $clone->toString());
        $this->assertSame('magic', $clone->password);
    }

    #[Test]
    public function host_manipulation(): void
    {
        $uri = Uri::from('https://example.com');

        $this->assertSame('example.com', $uri->host);

        $clone = $uri->withHost('aureole.magic');
        $this->assertSame('https://aureole.magic', $clone->toString());
        $this->assertSame('aureole.magic', $clone->host);

        $this->assertSame('example.com', $uri->host);
    }

    #[Test]
    public function port_manipulation(): void
    {
        $uri = Uri::from('https://example.com');

        $this->assertNull($uri->port);

        $clone = $uri->withPort(8080);
        $this->assertSame('https://example.com:8080', $clone->toString());
        $this->assertSame(8080, $clone->port);

        $this->assertNull($uri->port);
    }

    #[Test]
    public function path_manipulation(): void
    {
        $uri = Uri::from('https://example.com');

        $this->assertNull($uri->path);

        $clone = $uri->withPath('foo/bar');
        $this->assertSame('https://example.com/foo/bar', $clone->toString());
        $this->assertSame('foo/bar', $clone->path);

        $this->assertNull($uri->path);
    }

    #[Test]
    public function query_manipulation(): void
    {
        $uri = Uri::from('https://example.com');

        $this->assertSame([], $uri->query);

        $clone = $uri->withQuery(destination: 'aureole', companion: 'fern');
        $this->assertSame('https://example.com?destination=aureole&companion=fern', $clone->toString());
        $this->assertSame(['destination' => 'aureole', 'companion' => 'fern'], $clone->query);

        $this->assertSame([], $uri->query);
    }

    #[Test]
    public function query_with_special_values(): void
    {
        $uri = Uri::from('https://example.com');

        $clone = $uri->withQuery('standalone');
        $this->assertSame('https://example.com?standalone', $clone->toString());

        $clone = $uri->withQuery(active: true, disabled: false);
        $this->assertSame('https://example.com?active=true&disabled=false', $clone->toString());
    }

    #[Test]
    public function fragment_manipulation(): void
    {
        $uri = Uri::from('https://example.com');

        $this->assertNull($uri->fragment);

        $clone = $uri->withFragment('adventure');
        $this->assertSame('https://example.com#adventure', $clone->toString());
        $this->assertSame('adventure', $clone->fragment);

        $this->assertNull($uri->fragment);
    }

    #[Test]
    public function segments_property(): void
    {
        $this->assertSame([], Uri::from('https://example.com')->segments);
        $this->assertSame([], Uri::from('https://example.com/')->segments);
        $this->assertSame(['journey'], Uri::from('https://example.com/journey')->segments);
        $this->assertSame(['journey', 'to', 'aureole'], Uri::from('https://example.com/journey/to/aureole')->segments);
        $this->assertSame(['journey', 'north'], Uri::from('https://example.com/journey/north/')->segments);
        $this->assertSame(['journey', 'north'], Uri::from('https://example.com//journey//north//')->segments);
    }

    #[Test]
    public function add_query(): void
    {
        $uri = Uri::from('https://example.com?existing=value');

        $clone = $uri->addQuery(destination: 'aureole', companion: 'fern');
        $this->assertSame('https://example.com?existing=value&destination=aureole&companion=fern', $clone->toString());
        $this->assertSame(['existing' => 'value', 'destination' => 'aureole', 'companion' => 'fern'], $clone->query);

        $this->assertSame(['existing' => 'value'], $uri->query);
    }

    #[Test]
    public function remove_query(): void
    {
        $uri = Uri::from('https://example.com?foo=bar&baz=qux');

        $this->assertSame(['foo' => 'bar', 'baz' => 'qux'], $uri->query);

        $clone = $uri->removeQuery();
        $this->assertSame('https://example.com', $clone->toString());
        $this->assertSame([], $clone->query);

        $this->assertSame(['foo' => 'bar', 'baz' => 'qux'], $uri->query);
    }

    #[Test]
    public function remove_query_when_there_was_no_query(): void
    {
        $uri = Uri::from('https://example.com');
        $withoutQuery = $uri->removeQuery();

        $this->assertSame('https://example.com', $withoutQuery->toString());
        $this->assertSame([], $withoutQuery->query);
    }

    #[Test]
    public function without_query_removes_specific_parameters(): void
    {
        $uri = Uri::from('https://example.com?foo=bar&baz=qux&active=true');

        $clone = $uri->withoutQuery('foo');
        $this->assertSame('https://example.com?baz=qux&active=true', $clone->toString());
        $this->assertSame(['baz' => 'qux', 'active' => 'true'], $clone->query);

        $clone = $uri->withoutQuery('foo', 'baz');
        $this->assertSame('https://example.com?active=true', $clone->toString());
        $this->assertSame(['active' => 'true'], $clone->query);

        $clone = $uri->withoutQuery(foo: 'bar');
        $this->assertSame('https://example.com?baz=qux&active=true', $clone->toString());
        $this->assertSame(['baz' => 'qux', 'active' => 'true'], $clone->query);

        $clone = $uri->withoutQuery(foo: 'different');
        $this->assertSame('https://example.com?foo=bar&baz=qux&active=true', $clone->toString());
        $this->assertSame(['foo' => 'bar', 'baz' => 'qux', 'active' => 'true'], $clone->query);

        $clone = $uri->withoutQuery('nonexistent');
        $this->assertSame('https://example.com?foo=bar&baz=qux&active=true', $clone->toString());

        $this->assertSame(['foo' => 'bar', 'baz' => 'qux', 'active' => 'true'], $uri->query);
    }

    #[Test]
    public function without_query_on_empty_uri(): void
    {
        $uri = Uri::from('https://example.com');

        $clone = $uri->withoutQuery('foo', 'bar');
        $this->assertSame('https://example.com', $clone->toString());
        $this->assertSame([], $clone->query);
    }

    #[Test]
    public function method_chaining(): void
    {
        $uri = Uri::from('https://old.example.com')
            ->withScheme('http')
            ->withHost('neue.com')
            ->withPort(8080)
            ->withUser('frieren')
            ->withPassword('magic')
            ->withPath('/journey')
            ->withQuery(destination: 'aureole', party: 'stark,fern')
            ->withFragment('adventure');

        $expected = 'http://frieren:magic@neue.com:8080/journey?destination=aureole&party=stark%2Cfern#adventure';
        $this->assertSame($expected, $uri->toString());
    }

    #[Test]
    public function immutability(): void
    {
        $original = Uri::from('https://example.com/path?foo=bar#fragment');
        $modified = $original
            ->withScheme('http')
            ->withHost('andere.com')
            ->withPort(9000)
            ->withPath('/new')
            ->withQuery(baz: 'qux')
            ->withFragment('new-fragment');

        $this->assertSame('https://example.com/path?foo=bar#fragment', $original->toString());
        $this->assertSame('http://andere.com:9000/new?baz=qux#new-fragment', $modified->toString());

        $this->assertSame('https', $original->scheme);
        $this->assertSame('example.com', $original->host);
        $this->assertNull($original->port);
        $this->assertSame('/path', $original->path);
        $this->assertSame(['foo' => 'bar'], $original->query);
        $this->assertSame('fragment', $original->fragment);
    }

    #[Test]
    public function edge_cases(): void
    {
        $uri = Uri::from('');
        $this->assertSame('', $uri->toString());

        $uri = Uri::from('malformed-uri');
        $this->assertSame('malformed-uri', $uri->toString());

        $complexUri = 'https://user:pass@example.com:8080/path?query=value#fragment';
        $uri = Uri::from($complexUri);
        $this->assertSame($complexUri, $uri->toString());
        $this->assertSame('https', $uri->scheme);
        $this->assertSame('user', $uri->user);
        $this->assertSame('pass', $uri->password);
        $this->assertSame('example.com', $uri->host);
        $this->assertSame(8080, $uri->port);
        $this->assertSame('/path', $uri->path);
        $this->assertSame(['query' => 'value'], $uri->query);
        $this->assertSame('fragment', $uri->fragment);
    }
}
