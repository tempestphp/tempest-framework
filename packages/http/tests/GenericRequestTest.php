<?php

declare(strict_types=1);

namespace Tempest\Http\Tests;

use LogicException;
use PHPUnit\Framework\TestCase;
use Tempest\Http\ContentType;
use Tempest\Http\GenericRequest;
use Tempest\Http\Header;
use Tempest\Http\Method;

final class GenericRequestTest extends TestCase
{
    public function test_normalizes_header_access(): void
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

        $this->assertSame($upperCaseValue, $request->headers->get('UpperCase'));
        $this->assertEquals(
            new Header('uppercase', [$upperCaseValue]),
            $request->headers->getHeader('UpperCase'),
        );
    }

    public function test_throws_on_set(): void
    {
        $headers = new GenericRequest(
            method: Method::GET,
            uri: '/',
        )->headers;

        $this->expectException(LogicException::class);
        $headers['x'] = 'yes';
    }

    public function test_throws_on_unset(): void
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

    public function test_accepts_with_accept_header(): void
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

    public function test_accepts_with_no_accept_header(): void
    {
        $request = new GenericRequest(
            method: Method::GET,
            uri: '/',
        );

        $this->assertTrue($request->accepts(ContentType::JSON));
        $this->assertTrue($request->accepts(ContentType::HTML));
        $this->assertTrue($request->accepts(ContentType::XML));
    }

    public function test_accepts_with_empty_accept_header(): void
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

    public function test_accepts_with_wildcard(): void
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

    public function test_accepts_with_multiple_values(): void
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

    public function test_accepts_with_wildcard_subtype(): void
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

    public function test_accepts_evaluates_all_content_types(): void
    {
        $request = new GenericRequest(
            method: Method::GET,
            uri: '/',
            headers: [
                'Accept' => 'application/*, image/avif',
            ],
        );

        $this->assertFalse($request->accepts(ContentType::JSON, ContentType::HTML));
        $this->assertTrue($request->accepts(ContentType::JSON, ContentType::XML));
        $this->assertTrue($request->accepts(ContentType::JSON, ContentType::AVIF));
        $this->assertFalse($request->accepts(ContentType::AVIF, ContentType::PNG));
    }
}
