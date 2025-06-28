<?php

declare(strict_types=1);

namespace Tempest\HttpClient\Tests\Testing;

use GuzzleHttp\Psr7\HttpFactory;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Tempest\HttpClient\Testing\MockClient;

/**
 * @internal
 */
final class MockClientTest extends TestCase
{
    public function test_it_will_fake_exact_uris(): void
    {
        $response = MockClient::response(code: 201);

        $client = MockClient::fake([
            'example.com/some/path' => $response,
        ]);

        $request1 = $this->createRequest('GET', 'example.com/some/path');
        $request2 = $this->createRequest('GET', 'example.com/some/other/path');

        $this->assertSame($response, $client->sendRequest($request1));
        $this->assertNotSame($response, $client->sendRequest($request2));
    }

    public function test_it_will_fake_wildcard_uris(): void
    {
        $response1 = MockClient::response(code: 201);
        $response2 = MockClient::response(code: 301);

        $client = MockClient::fake([
            'example.com/*/testing' => $response1,
            'example.net/testing/*' => $response2,
        ]);

        $request1 = $this->createRequest('POST', 'example.com/some/testing');
        $request2 = $this->createRequest('GET', 'example.net/testing/something');

        $this->assertSame($response1, $client->sendRequest($request1));
        $this->assertSame($response2, $client->sendRequest($request2));
    }

    public function test_it_prefers_exact_uris_over_wildcard(): void
    {
        $response1 = MockClient::response(code: 200);
        $response2 = MockClient::response(code: 418);

        $client = MockClient::fake([
            'example.com/some/*' => $response1,
            'example.com/some/testing' => $response2,
        ]);

        $request = $this->createRequest('GET', 'example.com/some/testing');

        $this->assertSame($response2, $client->sendRequest($request));
    }

    public function test_it_will_return_responses_in_sequence(): void
    {
        $sequence = [
            MockClient::response(code: 200),
            MockClient::response(code: 201),
        ];

        $client = MockClient::fake([
            'example.com/testing' => MockClient::sequence($sequence),
        ]);

        $request = $this->createRequest('GET', 'example.com/testing');

        $this->assertSame($sequence[0], $client->sendRequest($request));
        $this->assertSame($sequence[1], $client->sendRequest($request));
        $this->assertSame($sequence[0], $client->sendRequest($request));
        $this->assertSame($sequence[1], $client->sendRequest($request));
    }

    public function test_it_will_return_responses_in_random_order(): void
    {
        $sequence = [
            MockClient::response(code: 200),
            MockClient::response(code: 201),
        ];

        $client = MockClient::fake([
            'example.com/*' => MockClient::random($sequence),
        ]);

        $request = $this->createRequest('GET', 'example.com/testing');

        $this->assertContains($client->sendRequest($request), $sequence);
        $this->assertContains($client->sendRequest($request), $sequence);
        $this->assertContains($client->sendRequest($request), $sequence);
        $this->assertContains($client->sendRequest($request), $sequence);
    }

    public function test_it_will_default_to_okay_response(): void
    {
        $response = MockClient::response();

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_it_will_create_response_body_from_file(): void
    {
        $response = MockClient::response(__DIR__ . '/Fixtures/response.json');

        $this->assertStringEqualsFileCanonicalizing(
            __DIR__ . '/Fixtures/response.json',
            $response->getBody()->getContents(),
        );
    }

    public function test_it_will_create_response_body_from_string(): void
    {
        $response = MockClient::response('Hello, world!');

        $this->assertSame('Hello, world!', $response->getBody()->getContents());
    }

    public function test_it_will_create_response_body_from_array(): void
    {
        $response = MockClient::response(['test' => 'value']);

        $this->assertSame('{"test":"value"}', $response->getBody()->getContents());
    }

    public function test_it_will_create_response_with_headers(): void
    {
        $response = MockClient::response(headers: [
            'test-header' => 'test-value',
        ]);

        $this->assertSame('test-value', $response->getHeaderLine('test-header'));
    }

    public function test_it_passes_request_uri_assertions(): void
    {
        $client = new MockClient();

        $client->sendRequest(
            $this->createRequest('GET', 'https://example.com'),
        );

        $client->assertUri('https://example.com');
    }

    public function test_it_fails_uri_assertions(): void
    {
        $this->expectException(ExpectationFailedException::class);

        $client = new MockClient();

        $client->sendRequest(
            $this->createRequest('GET', 'https://example.com'),
        );

        $client->assertUri('https://example.net');
    }

    public function test_it_passes_request_method_assertions(): void
    {
        $client = new MockClient();

        $client->sendRequest(
            $this->createRequest('GET', 'https://example.com'),
        );

        $client->assertMethod('GET');
    }

    public function test_it_fails_request_method_assertions(): void
    {
        $this->expectException(ExpectationFailedException::class);

        $client = new MockClient();

        $client->sendRequest(
            $this->createRequest('GET', 'https://example.com'),
        );

        $client->assertMethod('POST');
    }

    public function test_it_passes_header_equals_assertions(): void
    {
        $client = new MockClient();

        $request = $this->createRequest('GET', 'https://example.com')
            ->withHeader('x-api-key', 'ABC-123-XYZ');

        $client->sendRequest($request);

        $client->assertHeaderEquals('x-api-key', 'ABC-123-XYZ');
    }

    public function test_it_fails_header_equals_assertions(): void
    {
        $this->expectException(ExpectationFailedException::class);

        $client = new MockClient();

        $request = $this->createRequest('GET', 'https://example.com');

        $client->sendRequest($request);

        $client->assertHeaderEquals('x-api-key', 'ABC-123-XYZ');
    }

    public function test_it_passes_body_is_assertions(): void
    {
        $client = new MockClient();
        $streamFactory = new HttpFactory();

        $client->sendRequest(
            $this->createRequest('POST', 'https://example.com')
                ->withBody(
                    $streamFactory->createStream('{"key":"value"}'),
                ),
        );

        $client->assertBodyIs('{"key":"value"}');
    }

    public function test_it_fails_body_is_assertions(): void
    {
        $this->expectException(ExpectationFailedException::class);

        $client = new MockClient();
        $streamFactory = new HttpFactory();

        $client->sendRequest(
            $this->createRequest('POST', 'https://example.com')
                ->withBody(
                    $streamFactory->createStream('{"key":"value"}'),
                ),
        );

        $client->assertBodyIs('{"value":"key"}');
    }

    public function test_it_passes_body_is_empty_assertions(): void
    {
        $client = new MockClient();

        $client->sendRequest(
            $this->createRequest('POST', 'https://example.com'),
        );

        $client->assertBodyIsEmpty();
    }

    public function test_it_fails_body_is_empty_assertions(): void
    {
        $this->expectException(ExpectationFailedException::class);

        $client = new MockClient();
        $streamFactory = new HttpFactory();

        $client->sendRequest(
            $this->createRequest('POST', 'https://example.com')
                ->withBody(
                    $streamFactory->createStream('{"key":"value"}'),
                ),
        );

        $client->assertBodyIsEmpty();
    }

    public function test_it_passes_body_contains_assertions(): void
    {
        $client = new MockClient();
        $streamFactory = new HttpFactory();

        $client->sendRequest(
            $this->createRequest('POST', 'https://example.com')
                ->withBody(
                    $streamFactory->createStream('{"key":"value"}'),
                ),
        );

        $client->assertBodyContains('value');
    }

    public function test_it_fails_body_contains_assertions(): void
    {
        $this->expectException(ExpectationFailedException::class);

        $client = new MockClient();
        $streamFactory = new HttpFactory();

        $client->sendRequest(
            $this->createRequest('POST', 'https://example.com')
                ->withBody(
                    $streamFactory->createStream('{"key":"value"}'),
                ),
        );

        $client->assertBodyContains('something');
    }

    public function test_it_passes_requests_were_made_assertions(): void
    {
        $client = new MockClient();

        $client->sendRequest(
            $this->createRequest('GET', 'https://example.com'),
        );

        $client->assertRequestsWereMade(1);
    }

    public function test_it_fails_requests_were_made_assertions(): void
    {
        $this->expectException(ExpectationFailedException::class);

        new MockClient()->assertRequestsWereMade();
    }

    public function test_it_passes_no_requests_were_made_assertion(): void
    {
        new MockClient()->assertNoRequestsWereMade();
    }

    public function test_it_fails_no_requests_were_made_assertion(): void
    {
        $this->expectException(ExpectationFailedException::class);

        $client = new MockClient();

        $client->sendRequest(
            $this->createRequest('GET', 'https://example.com'),
        );

        $client->assertNoRequestsWereMade();
    }

    private function createRequest(string $method, string $uri): RequestInterface
    {
        $factory = new HttpFactory();

        return $factory->createRequest($method, $uri);
    }
}
