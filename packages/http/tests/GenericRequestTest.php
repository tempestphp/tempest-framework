<?php

declare(strict_types=1);

namespace Tempest\Http\Tests;

use LogicException;
use PHPUnit\Framework\TestCase;
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
}
