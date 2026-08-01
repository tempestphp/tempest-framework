<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Http\GenericRequest;
use Tempest\Http\Mappers\PsrRequestToGenericRequestMapper;
use Tempest\Http\Mappers\RequestToPsrRequestMapper;
use Tempest\Http\Method;
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

        $this->assertSame('203.0.113.9', $request->ip);
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
    public function requests_without_an_ip_are_dispatched_normally(): void
    {
        $this->http->get('/ip')->assertSee('unknown');
    }
}
