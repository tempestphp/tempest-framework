<?php

declare(strict_types=1);

namespace Tempest\Http\Tests;

use LogicException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Http\ContentType;
use Tempest\Http\GenericRequest;
use Tempest\Http\Header;
use Tempest\Http\Method;

final class GenericRequestTest extends TestCase
{
    #[Test]
    public function normalizes_header_access(): void
    {
        $upperCaseValue = 'UpperCase';
        $lowerCaseValue = 'LowerCase';

        $request = new GenericRequest(
            method: Method::GET,
            uri: '/',
            headers: [
                'UPPERCASE' => $upperCaseValue,
                'lowercase' => $lowerCaseValue,
            ],
        );

        $this->assertSame($upperCaseValue, $request->headers['uppercase']);
        $this->assertSame($upperCaseValue, $request->headers['UPPerCasE']);
        $this->assertSame($lowerCaseValue, $request->headers['lowercase']);
        $this->assertNull($request->headers['missing']);

        $this->assertSame($upperCaseValue, $request->headers->get('UpperCase'));
        $this->assertEquals(
            new Header('uppercase', [$upperCaseValue]),
            $request->headers->getHeader('UpperCase'),
        );
    }

    #[Test]
    public function throws_on_set(): void
    {
        $headers = new GenericRequest(
            method: Method::GET,
            uri: '/',
        )->headers;

        $this->expectException(LogicException::class);
        $headers['x'] = 'yes';
    }

    #[Test]
    public function throws_on_unset(): void
    {
        $headers = new GenericRequest(
            method: Method::GET,
            uri: '/',
            headers: [
                'x' => 'yes',
            ],
        )->headers;

        $this->expectException(LogicException::class);
        unset($headers['x']);
    }

    #[Test]
    public function accepts_with_accept_header(): void
    {
        $request = new GenericRequest(
            method: Method::GET,
            uri: '/',
            headers: [
                'Accept' => 'application/json',
            ],
        );

        $this->assertTrue($request->accepts(ContentType::JSON));
        $this->assertFalse($request->accepts(ContentType::HTML));
        $this->assertFalse($request->accepts(ContentType::XML));
    }

    #[Test]
    public function accepts_with_no_accept_header(): void
    {
        $request = new GenericRequest(
            method: Method::GET,
            uri: '/',
        );

        $this->assertTrue($request->accepts(ContentType::JSON));
        $this->assertTrue($request->accepts(ContentType::HTML));
        $this->assertTrue($request->accepts(ContentType::XML));
    }

    #[Test]
    public function accepts_with_empty_accept_header(): void
    {
        $request = new GenericRequest(
            method: Method::GET,
            uri: '/',
            headers: [
                'Accept' => '',
            ],
        );

        $this->assertTrue($request->accepts(ContentType::JSON));
        $this->assertTrue($request->accepts(ContentType::HTML));
        $this->assertTrue($request->accepts(ContentType::XML));
    }

    #[Test]
    public function accepts_with_wildcard(): void
    {
        $request = new GenericRequest(
            method: Method::GET,
            uri: '/',
            headers: [
                'Accept' => '*/*',
            ],
        );

        $this->assertTrue($request->accepts(ContentType::JSON));
        $this->assertTrue($request->accepts(ContentType::HTML));
        $this->assertTrue($request->accepts(ContentType::XML));
    }

    #[Test]
    public function accepts_with_multiple_values(): void
    {
        $request = new GenericRequest(
            method: Method::GET,
            uri: '/',
            headers: [
                'Accept' => 'application/json, text/html',
            ],
        );

        $this->assertTrue($request->accepts(ContentType::JSON));
        $this->assertTrue($request->accepts(ContentType::HTML));
        $this->assertFalse($request->accepts(ContentType::XML));
    }

    #[Test]
    public function accepts_with_wildcard_subtype(): void
    {
        $request = new GenericRequest(
            method: Method::GET,
            uri: '/',
            headers: [
                'Accept' => 'application/*',
            ],
        );

        $this->assertTrue($request->accepts(ContentType::JSON));
        $this->assertFalse($request->accepts(ContentType::HTML));
        $this->assertTrue($request->accepts(ContentType::XML));
    }

    #[Test]
    public function accepts_can_handle_priorities(): void
    {
        $request = new GenericRequest(
            method: Method::GET,
            uri: '/',
            headers: [
                'Accept' => 'text/html, application/xhtml+xml;q=0.8, application/xml;q=0.8, image/webp',
            ],
        );

        $this->assertTrue($request->accepts(ContentType::XHTML));
        $this->assertTrue($request->accepts(ContentType::XML));
    }

    #[Test]
    public function accepts_returns_true_on_first_match(): void
    {
        $request = new GenericRequest(
            method: Method::GET,
            uri: '/',
            headers: [
                'Accept' => 'application/*, image/avif',
            ],
        );

        $this->assertTrue($request->accepts(ContentType::HTML, ContentType::JSON));
        $this->assertTrue($request->accepts(ContentType::XML, ContentType::JSON));
        $this->assertTrue($request->accepts(ContentType::JSON, ContentType::AVIF));
        $this->assertTrue($request->accepts(ContentType::AVIF, ContentType::PNG));
        $this->assertFalse($request->accepts(ContentType::HTML, ContentType::PNG));
    }
}
