<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Route;

use PHPUnit\Framework\Attributes\Test;
use Tempest\DateTime\Duration;
use Tempest\Http\Status;
use Tempest\Router\UriGenerator;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Route\Fixtures\SignedUrlController;

final class ValidSignatureMiddlewareTest extends FrameworkIntegrationTestCase
{
    private UriGenerator $generator {
        get => $this->container->get(UriGenerator::class);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->registerRoute([SignedUrlController::class, 'signedAction']);
        $this->registerRoute([SignedUrlController::class, 'unsignedAction']);
    }

    #[Test]
    public function valid_signature_allows_request(): void
    {
        $uri = $this->generator->createSignedUri(
            action: [SignedUrlController::class, 'signedAction'],
            token: 'abc123',
        );

        $response = $this->http->get($uri);

        $this->assertSame(Status::OK, $response->status);
        $body = is_array($response->body) ? $response->body : json_decode($response->body, true);
        $this->assertSame('abc123', $body['token']);
        $this->assertSame('Signature valid', $body['message']);
    }

    #[Test]
    public function missing_signature_returns_forbidden(): void
    {
        $response = $this->http->get('/signed-action/abc123');

        $this->assertSame(Status::FORBIDDEN, $response->status);
    }

    #[Test]
    public function invalid_signature_returns_forbidden(): void
    {
        $uri = $this->generator->createSignedUri(
            action: [SignedUrlController::class, 'signedAction'],
            token: 'abc123',
        );

        // Tamper with the signature
        $tamperedUri = str_replace('signature=', 'signature=tampered', $uri);

        $response = $this->http->get($tamperedUri);

        $this->assertSame(Status::FORBIDDEN, $response->status);
    }

    #[Test]
    public function tampered_parameter_returns_forbidden(): void
    {
        $uri = $this->generator->createSignedUri(
            action: [SignedUrlController::class, 'signedAction'],
            token: 'abc123',
        );

        // Tamper with the token parameter
        $tamperedUri = str_replace('abc123', 'tampered', $uri);

        $response = $this->http->get($tamperedUri);

        $this->assertSame(Status::FORBIDDEN, $response->status);
    }

    #[Test]
    public function expired_signature_returns_forbidden(): void
    {
        $clock = $this->clock();

        $uri = $this->generator->createTemporarySignedUri(
            action: [SignedUrlController::class, 'signedAction'],
            duration: Duration::minutes(10),
            token: 'abc123',
        );

        // Advance time past expiration
        $clock->plus(Duration::minutes(15));

        $response = $this->http->get($uri);

        $this->assertSame(Status::FORBIDDEN, $response->status);
    }

    #[Test]
    public function temporary_signature_valid_before_expiration(): void
    {
        $clock = $this->clock();

        $uri = $this->generator->createTemporarySignedUri(
            action: [SignedUrlController::class, 'signedAction'],
            duration: Duration::minutes(10),
            token: 'abc123',
        );

        // Advance time but stay within expiration
        $clock->plus(Duration::minutes(5));

        $response = $this->http->get($uri);

        $this->assertSame(Status::OK, $response->status);
    }

    #[Test]
    public function unsigned_route_works_without_signature(): void
    {
        // Routes without #[ValidSignature] should work normally
        $response = $this->http->get('/unsigned-action/abc123');

        $this->assertSame(Status::OK, $response->status);
    }

    #[Test]
    public function tampered_expiration_returns_forbidden(): void
    {
        $clock = $this->clock();

        $uri = $this->generator->createTemporarySignedUri(
            action: [SignedUrlController::class, 'signedAction'],
            duration: Duration::minutes(10),
            token: 'abc123',
        );

        // Get the current timestamp and extend it in the URL
        $timestamp = $clock->now()->plusMinutes(10)->getTimestamp()->getSeconds();
        $tamperedUri = str_replace(
            'expires_at=' . $timestamp,
            'expires_at=' . ($timestamp + 3600),
            $uri
        );

        $response = $this->http->get($tamperedUri);

        $this->assertSame(Status::FORBIDDEN, $response->status);
    }
}
