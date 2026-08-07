<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Http\GenericRequest;
use Tempest\Http\Ip\TrustedProxiesConfig;
use Tempest\Http\Mappers\PsrRequestToGenericRequestMapper;
use Tempest\Http\Mappers\RequestToPsrRequestMapper;
use Tempest\Http\Method;
use Tempest\Support\Ip\IpAddress;
use Tests\Tempest\Fixtures\Requests\BookRequest;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Mapper\map;

/**
 * @internal
 */
final class RequestIpTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function ip_is_read_from_the_server_parameters(): void
    {
        $psrRequest = $this->http->fromIp('203.0.113.9')->makePsrRequest('/');

        $request = map($psrRequest)->with(PsrRequestToGenericRequestMapper::class)->do();

        $this->assertInstanceOf(IpAddress::class, $request->ip);
        $this->assertTrue($request->ip->equals('203.0.113.9'));
    }

    #[Test]
    public function ip_is_null_when_the_server_does_not_report_one(): void
    {
        $request = map($this->http->makePsrRequest('/'))->with(PsrRequestToGenericRequestMapper::class)->do();

        $this->assertNull($request->ip);
    }

    #[Test]
    public function ip_survives_the_round_trip_to_a_psr_request(): void
    {
        $request = new GenericRequest(method: Method::GET, uri: '/', ip: '203.0.113.9');

        $psrRequest = map($request)->with(RequestToPsrRequestMapper::class)->do();

        $this->assertSame('203.0.113.9', $psrRequest->getServerParams()['REMOTE_ADDR']);
    }

    #[Test]
    public function ip_is_available_to_a_controller(): void
    {
        $this->http->fromIp('203.0.113.9')->get('/ip')->assertSee('203.0.113.9');
    }

    #[Test]
    public function ip_is_carried_over_to_a_custom_request(): void
    {
        $request = new GenericRequest(method: Method::POST, uri: '/', body: ['title' => 'Timeline Taxi'], ip: '203.0.113.9');

        $bookRequest = map($request)->to(BookRequest::class);

        $this->assertInstanceOf(IpAddress::class, $bookRequest->ip);
        $this->assertTrue($bookRequest->ip->equals('203.0.113.9'));
    }

    #[Test]
    public function ip_is_accepted_as_a_value_object(): void
    {
        $request = new GenericRequest(method: Method::GET, uri: '/', ip: IpAddress::from('203.0.113.9'));

        $this->assertTrue($request->ip->equals('203.0.113.9'));
    }

    #[Test]
    public function ip_is_compared_regardless_of_notation(): void
    {
        $request = new GenericRequest(method: Method::GET, uri: '/', ip: '::ffff:203.0.113.9');

        $this->assertTrue($request->ip->equals('203.0.113.9'));
    }

    #[Test]
    public function requests_without_an_ip_are_dispatched_normally(): void
    {
        $this->http->get('/ip')->assertSee('unknown');
    }

    #[Test]
    public function forwarding_headers_are_ignored_by_default(): void
    {
        $this->http
            ->fromIp('10.0.0.1')
            ->get('/ip', headers: ['X-Forwarded-For' => '198.51.100.7'])
            ->assertSee('10.0.0.1');
    }

    #[Test]
    public function forwarding_headers_are_read_from_a_trusted_proxy(): void
    {
        $this->container->config(new TrustedProxiesConfig(proxies: ['10.0.0.0/8']));

        $this->http
            ->fromIp('10.0.0.1')
            ->get('/ip', headers: ['X-Forwarded-For' => '198.51.100.7'])
            ->assertSee('198.51.100.7');
    }
}
