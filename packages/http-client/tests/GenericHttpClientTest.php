<?php

declare(strict_types=1);

namespace Tempest\HttpClient\Tests;

use GuzzleHttp\Psr7\HttpFactory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Http\GenericRequest;
use Tempest\Http\Method;
use Tempest\HttpClient\Driver\Psr18Driver;
use Tempest\HttpClient\GenericHttpClient;
use Tempest\HttpClient\HttpClient;
use Tempest\HttpClient\Testing\MockClient;

/**
 * @internal
 */
final class GenericHttpClientTest extends TestCase
{
    private HttpClient $client;

    private MockClient $mock;

    private HttpFactory $factory;

    #[Test]
    public function send_request_proxies_to_http_client(): void
    {
        $request = new GenericRequest(method: Method::PUT, uri: '/testing-put', body: []);

        $this->client->sendRequest($request);

        $this->mock
            ->assertMethod('PUT')
            ->assertUri('/testing-put');
    }

    #[Test]
    public function get_proxies_to_http_client(): void
    {
        $this->client->get('/test-get');

        $this->mock
            ->assertMethod('GET')
            ->assertUri('/test-get');
    }

    #[Test]
    public function get_with_headers_proxies_to_http_client_with_headers(): void
    {
        $this->client->get('/test-get-with-headers', [
            'X-Tempest' => 'We love Tempest!',
        ]);

        $this->mock
            ->assertMethod('GET')
            ->assertHeaderEquals('X-Tempest', 'We love Tempest!');
    }

    #[Test]
    public function head_proxies_to_http_client(): void
    {
        $this->client->head('/test-head');

        $this->mock
            ->assertMethod('HEAD')
            ->assertUri('/test-head');
    }

    #[Test]
    public function post_proxies_to_http_client(): void
    {
        $this->client->post(uri: '/test-post', body: '{"test":"value"}');

        $this->mock
            ->assertMethod('POST')
            ->assertUri('/test-post')
            ->assertBodyIs('{"test":"value"}');
    }

    #[Test]
    public function query_proxies_to_http_client(): void
    {
        $this->client->query(
            uri: '/test-query',
            headers: ['Content-Type' => 'application/json'],
            body: '{"filter":"active"}',
        );

        $this->mock
            ->assertMethod('QUERY')
            ->assertUri('/test-query')
            ->assertHeaderEquals('Content-Type', 'application/json')
            ->assertBodyIs('{"filter":"active"}');
    }

    #[Test]
    public function trace_proxies_to_http_client(): void
    {
        $this->client->trace('/test-trace');

        $this->mock
            ->assertMethod('TRACE')
            ->assertUri('/test-trace');
    }

    #[Test]
    public function put_proxies_to_http_client(): void
    {
        $this->client->put(uri: '/test-put', body: '{"test":"test-value"}');

        $this->mock
            ->assertMethod('PUT')
            ->assertUri('/test-put')
            ->assertBodyIs('{"test":"test-value"}');
    }

    #[Test]
    public function patch_proxies_to_http_client(): void
    {
        $this->client->patch(uri: '/test-patch', body: '{"firstName":"Dwight"}');

        $this->mock
            ->assertMethod('PATCH')
            ->assertUri('/test-patch')
            ->assertBodyIs('{"firstName":"Dwight"}');
    }

    #[Test]
    public function delete_proxies_to_http_client(): void
    {
        $this->client->delete(uri: '/test-delete');

        $this->mock
            ->assertMethod('DELETE')
            ->assertUri('/test-delete');
    }

    #[Test]
    public function options_proxies_to_http_client(): void
    {
        $this->client->options('/test-options');

        $this->mock
            ->assertMethod('OPTIONS')
            ->assertUri('/test-options');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = new HttpFactory();

        $this->mock = new MockClient(
            responseFactory: $this->factory,
            streamFactory: $this->factory,
        );

        $psr18Driver = new Psr18Driver(
            client: $this->mock,
            uriFactory: $this->factory,
            requestFactory: $this->factory,
            streamFactory: $this->factory,
        );

        $this->client = new GenericHttpClient($psr18Driver);
    }
}
